<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);
        $superAdmin->syncPermissions(Permission::all());

        $admin = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);
        $admin->syncPermissions([
            'users.view',
            'users.create',
            'users.edit',
            'roles.view',
            'audit.view',
        ]);

        $manager = Role::firstOrCreate([
            'name' => 'Manager',
            'guard_name' => 'web',
        ]);
        $manager->syncPermissions([
            'users.view',
            'audit.view',
        ]);

        Role::firstOrCreate([
            'name' => 'User',
            'guard_name' => 'web',
        ]);
    }
}
