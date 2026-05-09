<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PermissionResolver
{
    public function has(User $user, string $permission): bool
    {
        if ($this->directDeniedPermissions($user)->contains($permission)) {
            return false;
        }

        return $this->permissions($user)->contains($permission);
    }

    public function hasInOrganization(
        User $user,
        Organization $organization,
        string $permission
    ): bool {
        $organizationIds = $this->organizationAndAncestorIds($organization);

        if (
            $this->directDeniedPermissionsInOrganization($user, $organizationIds)
                ->contains($permission)
        ) {
            return false;
        }

        return $this->permissionsInOrganization($user, $organizationIds)
            ->contains($permission);
    }

    public function permissions(User $user): Collection
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
                    ->merge($this->directAllowedPermissionCodes($member));
            })
            ->unique()
            ->values();
    }

    private function permissionsInOrganization(User $user, array $organizationIds): Collection
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
                    ->merge($this->directAllowedPermissionCodes($member, $organizationIds));
            })
            ->unique()
            ->values();
    }

    private function rolePermissionCodes($member, ?array $organizationIds = null): Collection
    {
        return $member->roles
            ->filter(fn ($memberRole) => $this->isGrantActive($memberRole))
            ->filter(fn ($memberRole) => $this->matchesOrganizationScope($memberRole, $organizationIds))
            ->flatMap(fn ($memberRole) => $memberRole->role?->permissions ?? collect())
            ->pluck('code');
    }

    private function directAllowedPermissionCodes($member, ?array $organizationIds = null): Collection
    {
        return $member->directPermissions
            ->filter(fn ($memberPermission) => $this->isGrantActive($memberPermission))
            ->filter(fn ($memberPermission) => $memberPermission->effect === 'allow')
            ->filter(fn ($memberPermission) => $this->matchesOrganizationScope($memberPermission, $organizationIds))
            ->pluck('permission.code');
    }

    private function directDeniedPermissions(User $user): Collection
    {
        return $user->memberAccounts()
            ->with('member.directPermissions.permission')
            ->get()
            ->flatMap(fn ($account) => $account->member?->directPermissions ?? collect())
            ->filter(fn ($memberPermission) => $this->isGrantActive($memberPermission))
            ->filter(fn ($memberPermission) => $memberPermission->effect === 'deny')
            ->pluck('permission.code')
            ->unique()
            ->values();
    }

    private function directDeniedPermissionsInOrganization(User $user, array $organizationIds): Collection
    {
        return $user->memberAccounts()
            ->with('member.directPermissions.permission')
            ->get()
            ->flatMap(fn ($account) => $account->member?->directPermissions ?? collect())
            ->filter(fn ($memberPermission) => $this->isGrantActive($memberPermission))
            ->filter(fn ($memberPermission) => $memberPermission->effect === 'deny')
            ->filter(fn ($memberPermission) => $this->matchesOrganizationScope($memberPermission, $organizationIds))
            ->pluck('permission.code')
            ->unique()
            ->values();
    }

    private function matchesOrganizationScope($grant, ?array $organizationIds): bool
    {
        if ($organizationIds === null) {
            return true;
        }

        if (! $grant->organization_id) {
            return true;
        }

        return in_array($grant->organization_id, $organizationIds, true);
    }

    private function organizationAndAncestorIds(Organization $organization): array
    {
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

        if ($grant->revoked_at && Carbon::parse($grant->revoked_at)->isPast()) {
            return false;
        }

        return true;
    }
}
