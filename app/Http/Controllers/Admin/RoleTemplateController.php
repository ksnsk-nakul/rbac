<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleTemplateController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/management/RoleTemplates', [
            'templates' => RoleTemplate::with('permissions')->orderBy('name')->get(),
            'permissions' => Permission::orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:role_templates,slug'],
            'description' => ['nullable', 'string'],
            'permissions' => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $template = RoleTemplate::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
        ]);

        $template->permissions()->sync($validated['permissions'] ?? []);

        return back();
    }

    public function apply(Request $request, RoleTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:roles,slug'],
            'route' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:roles,route'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'route' => $validated['route'],
            'is_subadmin' => false,
            'is_default' => false,
        ]);

        $role->permissions()->sync($template->permissions->pluck('id')->all());

        return back();
    }
}
