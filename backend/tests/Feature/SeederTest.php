<?php

namespace Tests\Feature;

use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_create_expected_roles_and_permissions(): void
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->assertDatabaseHas('roles', ['name' => 'Super Admin']);
        $this->assertDatabaseHas('roles', ['name' => 'Admin']);
        $this->assertDatabaseHas('roles', ['name' => 'Manager']);
        $this->assertDatabaseHas('roles', ['name' => 'User']);

        $this->assertDatabaseHas('permissions', ['name' => 'users.view']);
        $this->assertDatabaseHas('permissions', ['name' => 'users.create']);
        $this->assertDatabaseHas('permissions', ['name' => 'roles.manage']);
    }

    public function test_seeders_are_strictly_idempotent_when_run_multiple_times(): void
    {
        // First run
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $initialRoleCount = Role::count();
        $initialPermissionCount = Permission::count();

        // Second run
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->assertEquals($initialRoleCount, Role::count());
        $this->assertEquals($initialPermissionCount, Permission::count());
    }
}
