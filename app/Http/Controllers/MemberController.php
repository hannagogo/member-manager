<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Services\PermissionResolver;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::with([
            'accounts',
            'memberships.organization',
            'roles.role',
        ])->get();

        return view('members.index', compact('members'));
    }

    public function show(Member $member, PermissionResolver $resolver)
    {
        $member->load([
            'accounts',
            'memberships.organization',
            'roles.role.permissions',
            'directPermissions.permission',
            'directPermissions.organization',
        ]);

        $user = auth()->user();

        $effectivePermissions = $user
            ? collect([
                'member.view',
                'member.manage',
                'organization.view',
                'organization.manage',
                'mediawiki_read',
                'mediawiki_edit',
                'docs_read',
                'docs_edit',
                'role_manage',
            ])->mapWithKeys(fn ($permission) => [
                $permission => $resolver->trace($user, $permission),
            ])
            : collect();

        return view('members.show', compact('member', 'effectivePermissions'));
    }
}
