<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_inherits_permissions_via_assigned_role(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Admin']);
        $permission = Permission::create(['name' => 'users.view']);

        $role->givePermissionTo($permission);
        $user->assignRole($role);

        $this->assertTrue($user->hasPermissionTo('users.view'));
        $this->assertTrue($user->can('users.view'));
    }

    public function test_super_admin_role_bypasses_all_permission_checks_via_gate_before(): void
    {
        $user = User::factory()->create();
        $superAdminRole = Role::create(['name' => 'Super Admin']);

        $user->assignRole($superAdminRole);

        $this->assertTrue(Gate::forUser($user)->allows('unassigned.random.permission'));
        $this->assertTrue(Gate::forUser($user)->allows('any.domain.permission'));
    }

    public function test_regular_user_without_permission_fails_gate_check(): void
    {
        $user = User::factory()->create();

        $this->assertFalse(Gate::forUser($user)->allows('unassigned.random.permission'));
    }
}
