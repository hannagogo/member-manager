<?php

namespace App\Http\Controllers;

use App\Models\Organization;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::with('children')
            ->whereNull('parent_id')
            ->get();

        return view('organizations.index', compact('organizations'));
    }
}
