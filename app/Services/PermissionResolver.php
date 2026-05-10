<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PermissionResolver
{
    public function has(User $user, string $permission): bool
    {
        return $this->trace($user, $permission)['result'] === 'allow';
    }

    public function clearUserCache(User $user): void
    {
        Cache::forget($this->cacheKey($user));
    }

    public function hasInOrganization(
        User $user,
        Organization $organization,
        string $permission
    ): bool {
        return $this->traceInOrganization($user, $organization, $permission)['result'] === 'allow';
    }

    public function trace(User $user, string $permission): array
    {
        $cache = $this->permissionMap($user);

        $traces = $cache[$permission] ?? [];

        if (! is_array($traces) || array_is_list($traces) === false) {
            $traces = [$traces];
        }

        $result = collect($traces)
            ->contains(fn ($trace) => ($trace['result'] ?? null) === 'deny')
                ? 'deny'
                : (
                    collect($traces)
                        ->contains(fn ($trace) => ($trace['result'] ?? null) === 'allow')
                        ? 'allow'
                        : 'deny'
                );

        return [
            'result' => $result,
            'permission' => $permission,
            'traces' => $traces,
        ];
    }

    public function traceInOrganization(
        User $user,
        Organization $organization,
        string $permission
    ): array {

        $organizationIds = $this->organizationAndAncestorIds($organization);

        $deny = $this->findDirectDeny(
            $user,
            $permission,
            $organizationIds
        );

        if ($deny) {
            return $deny;
        }

        $allow = $this->findAllow(
            $user,
            $permission,
            $organizationIds
        );

        if ($allow) {
            return $allow;
        }

        return $this->traceResult(
            'deny',
            $permission,
            'none',
            null,
            $organization->name,
            'No matching permission grant in organization scope.'
        );
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

    private function findDirectDeny(
        User $user,
        string $permission,
        ?array $organizationIds = null
    ): ?array {
        $items = $user->memberAccounts()
            ->with('member.directPermissions.permission', 'member.directPermissions.organization')
            ->get()
            ->flatMap(fn ($account) => $account->member?->directPermissions ?? collect())
            ->filter(fn ($memberPermission) => $this->isGrantActive($memberPermission))
            ->filter(fn ($memberPermission) => $memberPermission->effect === 'deny')
            ->filter(fn ($memberPermission) => $this->matchesOrganizationScope($memberPermission, $organizationIds))
            ->filter(fn ($memberPermission) => $memberPermission->permission?->code === $permission);

        $deny = $items->first();

        if (! $deny) {
            return null;
        }

        return $this->traceResult(
            'deny',
            $permission,
            'direct_permission',
            $deny->permission?->code,
            $deny->organization?->name,
            $deny->reason
        );
    }

    private function findAllow(
        User $user,
        string $permission,
        ?array $organizationIds = null
    ): ?array {
        $accounts = $user->memberAccounts()
            ->with([
                'member.roles.role.permissions',
                'member.roles.organization',
                'member.directPermissions.permission',
                'member.directPermissions.organization',
            ])
            ->get();

        foreach ($accounts as $account) {
            $member = $account->member;

            if (! $member) {
                continue;
            }

            foreach ($member->roles as $memberRole) {
                if (! $this->isGrantActive($memberRole)) {
                    continue;
                }

                if (! $this->matchesOrganizationScope($memberRole, $organizationIds)) {
                    continue;
                }

                if ($memberRole->role?->permissions?->contains('code', $permission)) {
                    return $this->traceResult(
                        'allow',
                        $permission,
                        'role',
                        $memberRole->role?->code,
                        $memberRole->organization?->name,
                        null
                    );
                }
            }

            foreach ($member->directPermissions as $memberPermission) {
                if (! $this->isGrantActive($memberPermission)) {
                    continue;
                }

                if ($memberPermission->effect !== 'allow') {
                    continue;
                }

                if (! $this->matchesOrganizationScope($memberPermission, $organizationIds)) {
                    continue;
                }

                if ($memberPermission->permission?->code === $permission) {
                    return $this->traceResult(
                        'allow',
                        $permission,
                        'direct_permission',
                        $memberPermission->permission?->code,
                        $memberPermission->organization?->name,
                        $memberPermission->reason
                    );
                }
            }
        }

        return null;
    }


    private function permissionMap(User $user): array
    {
        return Cache::remember(
            $this->cacheKey($user),
            now()->addMinutes(10),
            fn () => $this->buildPermissionMap($user)
        );
    }

    private function buildPermissionMap(User $user): array
    {
        $map = [];

        $accounts = $user->memberAccounts()
            ->with([
                'member.roles.role.permissions',
                'member.roles.organization',
                'member.directPermissions.permission',
                'member.directPermissions.organization',
            ])
            ->get();

        foreach ($accounts as $account) {

            $member = $account->member;

            if (! $member) {
                continue;
            }

            foreach ($member->roles as $memberRole) {

                if (! $this->isGrantActive($memberRole)) {
                    continue;
                }

                foreach ($memberRole->role?->permissions ?? [] as $permission) {

                    $map[$permission->code] ??= [];

                    $map[$permission->code][] = $this->traceResult(
                        'allow',
                        $permission->code,
                        'role',
                        $memberRole->role?->code,
                        $memberRole->organization?->name,
                        null
                    );
                }
            }

            foreach ($member->directPermissions as $memberPermission) {

                if (! $this->isGrantActive($memberPermission)) {
                    continue;
                }

                $permission = $memberPermission->permission;

                if (! $permission) {
                    continue;
                }

                if ($memberPermission->effect === 'deny') {

                    $map[$permission->code] ??= [];

                    $map[$permission->code][] = $this->traceResult(
                        'deny',
                        $permission->code,
                        'direct_permission',
                        $permission->code,
                        $memberPermission->organization?->name,
                        $memberPermission->reason
                    );

                    continue;
                }

                $map[$permission->code] ??= [];

                $map[$permission->code][] = $this->traceResult(
                    'allow',
                    $permission->code,
                    'direct_permission',
                    $permission->code,
                    $memberPermission->organization?->name,
                    $memberPermission->reason
                );
            }
        }

        return $map;
    }

    private function cacheKey(User $user): string
    {
        return "permission-map:user:{$user->id}";
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

    private function traceResult(
        string $result,
        string $permission,
        string $sourceType,
        ?string $sourceName,
        ?string $organization,
        ?string $reason
    ): array {
        return [
            'result' => $result,
            'permission' => $permission,
            'source_type' => $sourceType,
            'source_name' => $sourceName,
            'organization' => $organization,
            'reason' => $reason,
        ];
    }
}
