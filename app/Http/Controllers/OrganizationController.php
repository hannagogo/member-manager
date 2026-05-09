<?php

namespace App\Http\Controllers;

use App\Models\Organization;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::with([
            'parent',
            'children',
            'memberships.member',
        ])->get();

        return view('organizations.index', compact('organizations'));
    }

    public function show(Organization $organization)
    {
        $organization->load([
            'parent',
            'children',
            'memberships.member',
            'memberRoles.role',
        ]);

        return view('organizations.show', compact('organization'));
    }
}
