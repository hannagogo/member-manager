<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;

class PermissionResolver
{
    public function has(User $user, string $permission): bool
    {
        return $this->permissions($user)
            ->contains($permission);
    }

    public function hasInOrganization(
        User $user,
        Organization $organization,
        string $permission
    ): bool {

        $organizationIds = $this->organizationAndAncestorIds($organization);

        return $this->permissionsInOrganization($user, $organizationIds)
            ->contains($permission);
    }

    public function permissions(User $user)
    {
        return $user->memberAccounts()
            ->with([
                'member.roles.role.permissions',
                'member.directPermissions.permission',
            ])
            ->get()
            ->flatMap(function ($account) {
                $member = $account->member;

                if (! $member) {
                    return collect();
                }

                return $this->rolePermissionCodes($member)
                    ->merge($this->directPermissionCodes($member));
            })
            ->unique()
            ->values();
    }

    private function permissionsInOrganization(User $user, array $organizationIds)
    {
        return $user->memberAccounts()
            ->with([
                'member.roles.role.permissions',
                'member.directPermissions.permission',
            ])
            ->get()
            ->flatMap(function ($account) use ($organizationIds) {
                $member = $account->member;

                if (! $member) {
                    return collect();
                }

                return $this->rolePermissionCodes($member, $organizationIds)
                    ->merge($this->directPermissionCodes($member, $organizationIds));
            })
            ->unique()
            ->values();
    }

    private function rolePermissionCodes($member, ?array $organizationIds = null)
    {
        return $member->roles
            ->filter(fn ($memberRole) => $this->isGrantActive($memberRole))
            ->filter(function ($memberRole) use ($organizationIds) {
                if ($organizationIds === null) {
                    return true;
                }

                if (! $memberRole->organization_id) {
                    return true;
                }

                return in_array($memberRole->organization_id, $organizationIds, true);
            })
            ->flatMap(fn ($memberRole) => $memberRole->role?->permissions ?? collect())
            ->pluck('code');
    }

    private function directPermissionCodes($member, ?array $organizationIds = null)
    {
        return $member->directPermissions
            ->filter(fn ($memberPermission) => $this->isGrantActive($memberPermission))
            ->filter(function ($memberPermission) use ($organizationIds) {
                if ($organizationIds === null) {
                    return true;
                }

                if (! $memberPermission->organization_id) {
                    return true;
                }

                return in_array($memberPermission->organization_id, $organizationIds, true);
            })
            ->pluck('permission.code');
    }

    private function organizationAndAncestorIds(
        Organization $organization
    ): array {

        $ids = [];

        $current = $organization;

        while ($current) {
            $ids[] = $current->id;
            $current = $current->parent;
        }

        return $ids;
    }

    private function isGrantActive($grant): bool
    {
        if ($grant->status !== 'active') {
            return false;
        }

        if (
            $grant->revoked_at &&
            Carbon::parse($grant->revoked_at)->isPast()
        ) {
            return false;
        }

        return true;
    }
}
