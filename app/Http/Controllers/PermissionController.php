<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(): View
    {
        $this->authorize('view-permissions');

        $permissions = Permission::with('roles')->get()->groupBy(function($permission) {
            $parts = explode('-', $permission->name);
            return ucfirst($parts[0]);
        });

        return view('permissions.index', compact('permissions'));
    }
}
