<?php

namespace App\Http\Controllers;

use App\Models\Member;

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

    public function show(Member $member)
    {
        $member->load([
            'accounts',
            'memberships.organization',
            'roles.role',
        ]);

        return view('members.show', compact('member'));
    }
}
