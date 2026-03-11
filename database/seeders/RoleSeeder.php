<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleTemplate;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed application roles.
     */
    public function run(): void
    {
        $superAdminRole = Role::firstOrCreate(
            ['slug' => 'super_admin'],
            [
                'name' => 'Super Admin',
                'is_subadmin' => false,
                'route' => 'admin',
                'is_default' => true,
                'mfa_required' => true,
            ]
        );

        $permissions = [
            ['name' => 'Read dashboard', 'slug' => 'dashboard.read', 'main_group' => 'dashboard'],
            ['name' => 'View accounts', 'slug' => 'accounts.view', 'main_group' => 'accounts'],
            ['name' => 'Update accounts', 'slug' => 'accounts.update', 'main_group' => 'accounts'],
            ['name' => 'Account security', 'slug' => 'accounts.security', 'main_group' => 'accounts'],
            ['name' => 'View roles', 'slug' => 'roles.view', 'main_group' => 'roles'],
            ['name' => 'Create roles', 'slug' => 'roles.create', 'main_group' => 'roles'],
            ['name' => 'Edit roles', 'slug' => 'roles.edit', 'main_group' => 'roles'],
            ['name' => 'Delete roles', 'slug' => 'roles.delete', 'main_group' => 'roles'],
            ['name' => 'View permissions', 'slug' => 'permissions.view', 'main_group' => 'permissions'],
            ['name' => 'Assign permissions', 'slug' => 'permissions.assign', 'main_group' => 'permissions'],
            ['name' => 'View audit logs', 'slug' => 'audit.view', 'main_group' => 'audit'],
            ['name' => 'Export audit logs', 'slug' => 'audit.export', 'main_group' => 'audit'],
            ['name' => 'View API tokens', 'slug' => 'api_tokens.view', 'main_group' => 'api'],
            ['name' => 'Create API tokens', 'slug' => 'api_tokens.create', 'main_group' => 'api'],
            ['name' => 'Revoke API tokens', 'slug' => 'api_tokens.revoke', 'main_group' => 'api'],
            ['name' => 'View system settings', 'slug' => 'system.settings.view', 'main_group' => 'system'],
            ['name' => 'Update system settings', 'slug' => 'system.settings.update', 'main_group' => 'system'],
            ['name' => 'View sessions', 'slug' => 'security.sessions.view', 'main_group' => 'security'],
            ['name' => 'Revoke sessions', 'slug' => 'security.sessions.revoke', 'main_group' => 'security'],
            ['name' => 'View IP allowlist', 'slug' => 'security.ip_allowlist.view', 'main_group' => 'security'],
            ['name' => 'Manage IP allowlist', 'slug' => 'security.ip_allowlist.manage', 'main_group' => 'security'],
            ['name' => 'Manage MFA', 'slug' => 'security.mfa.manage', 'main_group' => 'security'],
            ['name' => 'View approvals', 'slug' => 'approvals.view', 'main_group' => 'approvals'],
            ['name' => 'Approve requests', 'slug' => 'approvals.approve', 'main_group' => 'approvals'],
            ['name' => 'View role templates', 'slug' => 'templates.view', 'main_group' => 'templates'],
            ['name' => 'Create role templates', 'slug' => 'templates.create', 'main_group' => 'templates'],
            ['name' => 'Apply role templates', 'slug' => 'templates.apply', 'main_group' => 'templates'],
            ['name' => 'View webhooks', 'slug' => 'webhooks.view', 'main_group' => 'system'],
            ['name' => 'Manage webhooks', 'slug' => 'webhooks.manage', 'main_group' => 'system'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                ['name' => $permission['name'], 'main_group' => $permission['main_group']]
            );
        }

        $superAdminRole->permissions()->sync(Permission::pluck('id')->all());

        Plan::firstOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter',
                'audit_retention_days' => 30,
                'max_admin_users' => 1,
                'allow_api_tokens' => false,
                'allow_ip_allowlist' => false,
                'allow_mfa_enforcement' => true,
            ]
        );

        Plan::firstOrCreate(
            ['slug' => 'growth'],
            [
                'name' => 'Growth',
                'audit_retention_days' => 180,
                'max_admin_users' => 5,
                'allow_api_tokens' => true,
                'allow_ip_allowlist' => false,
                'allow_mfa_enforcement' => true,
            ]
        );

        Plan::firstOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise',
                'audit_retention_days' => 365,
                'max_admin_users' => 50,
                'allow_api_tokens' => true,
                'allow_ip_allowlist' => true,
                'allow_mfa_enforcement' => true,
            ]
        );

        $templates = [
            'support-admin' => [
                'name' => 'Support Admin',
                'description' => 'Manage accounts and view audit events.',
                'permissions' => ['dashboard.read', 'accounts.view', 'accounts.security', 'audit.view'],
            ],
            'content-manager' => [
                'name' => 'Content Manager',
                'description' => 'Manage roles and permissions for content teams.',
                'permissions' => ['dashboard.read', 'roles.view', 'roles.edit', 'permissions.view'],
            ],
            'billing-admin' => [
                'name' => 'Billing Admin',
                'description' => 'Access API tokens and system settings.',
                'permissions' => ['dashboard.read', 'api_tokens.view', 'api_tokens.create', 'api_tokens.revoke', 'system.settings.view'],
            ],
        ];

        foreach ($templates as $slug => $template) {
            $roleTemplate = RoleTemplate::firstOrCreate(
                ['slug' => $slug],
                ['name' => $template['name'], 'description' => $template['description']]
            );

            $permissionIds = Permission::whereIn('slug', $template['permissions'])->pluck('id')->all();
            $roleTemplate->permissions()->sync($permissionIds);
        }
    }
}
