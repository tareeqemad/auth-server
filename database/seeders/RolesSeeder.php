<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    private const PERMISSIONS = [
        'applications.view', 'applications.create', 'applications.update', 'applications.delete', 'applications.rotate_secret',
        'users.view', 'users.create', 'users.update', 'users.delete', 'users.reset_password',
        'audit_logs.view', 'audit_logs.export',
        'sessions.view', 'sessions.revoke',
        'settings.view', 'settings.update',
        'admins.view', 'admins.create', 'admins.update', 'admins.delete',
    ];

    private const ROLES = [
        'super_admin' => '*',
        'user_manager' => [
            'users.view', 'users.create', 'users.update', 'users.delete', 'users.reset_password',
            'audit_logs.view',
            'sessions.view', 'sessions.revoke',
        ],
        'client_manager' => [
            'applications.view', 'applications.create', 'applications.update', 'applications.delete', 'applications.rotate_secret',
            'audit_logs.view',
        ],
        'viewer' => [
            'applications.view',
            'users.view',
            'audit_logs.view',
            'sessions.view',
            'settings.view',
            'admins.view',
        ],
    ];

    public function run(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (self::ROLES as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if ($perms === '*') {
                $role->syncPermissions(Permission::where('guard_name', 'web')->get());
            } else {
                $role->syncPermissions($perms);
            }
        }

        $this->command->info('✅ تم إنشاء '.count(self::ROLES).' أدوار و '.count(self::PERMISSIONS).' صلاحية.');
    }
}
