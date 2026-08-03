<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_assigned_a_role(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Admin']);

        $user->assignRole($role);

        $this->assertTrue($user->hasRole('Admin'));
        $this->assertTrue($user->hasRole($role));
    }

    public function test_role_can_be_revoked_from_a_user(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'Manager']);

        $user->assignRole($role);
        $this->assertTrue($user->hasRole('Manager'));

        $user->removeRole('Manager');
        $this->assertFalse($user->fresh()->hasRole('Manager'));
    }

    public function test_user_roles_can_be_synced(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::create(['name' => 'Admin']);
        $userRole = Role::create(['name' => 'User']);

        $user->assignRole($adminRole);
        $user->syncRoles([$userRole]);

        $this->assertFalse($user->hasRole('Admin'));
        $this->assertTrue($user->hasRole('User'));
    }
}
