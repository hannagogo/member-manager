<?php

namespace App\Services;

use App\Models\User;

class PermissionResolver
{
    public function has(User $user, string $permission): bool
    {
        return $user->memberAccounts()
            ->with('member.roles.role.permissions')
            ->get()
            ->flatMap(fn ($account) => $account->member?->roles ?? [])
            ->flatMap(fn ($memberRole) => $memberRole->role?->permissions ?? [])
            ->contains('code', $permission);
    }

    public function permissions(User $user): array
    {
        return $user->memberAccounts()
            ->with('member.roles.role.permissions')
            ->get()
            ->flatMap(fn ($account) => $account->member?->roles ?? [])
            ->flatMap(fn ($memberRole) => $memberRole->role?->permissions ?? [])
            ->pluck('code')
            ->unique()
            ->values()
            ->all();
    }
}
