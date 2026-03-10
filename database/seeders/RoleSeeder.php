<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Seed application roles.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin', 'slug' => 'admin', 'is_subadmin' => false],
            ['name' => 'User', 'slug' => 'user', 'is_subadmin' => false],
            ['name' => 'Subadmin', 'slug' => 'subadmin', 'is_subadmin' => true],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                ['name' => $role['name'], 'is_subadmin' => $role['is_subadmin']]
            );
        }

        $permissions = [
            ['name' => 'Read dashboard', 'slug' => 'dashboard.read'],
            ['name' => 'Manage users', 'slug' => 'users.manage'],
            ['name' => 'Manage subadmins', 'slug' => 'subadmins.manage'],
            ['name' => 'Manage roles & permissions', 'slug' => 'roles.manage'],
            ['name' => 'View sitemap', 'slug' => 'sitemap.view'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                ['name' => $permission['name']]
            );
        }

        $adminRole = Role::where('slug', 'admin')->first();
        $userRole = Role::where('slug', 'user')->first();
        $subadminRole = Role::where('slug', 'subadmin')->first();

        if ($adminRole) {
            $adminRole->permissions()->sync(Permission::pluck('id')->all());
        }

        if ($userRole) {
            $userRole->permissions()->sync(
                Permission::whereIn('slug', ['dashboard.read'])->pluck('id')->all()
            );
        }

        if ($subadminRole) {
            $subadminRole->permissions()->sync(
                Permission::whereIn('slug', ['dashboard.read', 'users.manage'])->pluck('id')->all()
            );
        }

        if (! app()->environment(['local', 'development', 'dev'])) {
            return;
        }

        if (! $adminRole || ! $subadminRole) {
            return;
        }

        $hasAdmin = User::where('role_id', $adminRole->id)->exists();
        $hasSubadmin = User::where('role_id', $subadminRole->id)->exists();

        if (! $hasAdmin) {
            User::create([
                'name' => 'Demo Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('1234567890'),
                'role_id' => $adminRole->id,
            ]);
        }

        if (! $hasSubadmin) {
            User::create([
                'name' => 'Demo Subadmin',
                'email' => 'subadmin@admin.com',
                'password' => Hash::make('1234567890'),
                'role_id' => $subadminRole->id,
            ]);
        }
    }
}
