<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Organization\Infrastructure\Persistence\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrganizationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_organization(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/organizations', [
            'name' => 'Stark Industries',
            'slug' => 'stark-industries',
            'code' => 'STARK-01',
            'status' => 'active',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Organization created successfully',
                'data' => [
                    'name' => 'Stark Industries',
                    'slug' => 'stark-industries',
                    'code' => 'STARK-01',
                    'status' => 'active',
                ],
            ]);

        $this->assertNotNull($response->json('data.uuid'));
    }

    public function test_create_organization_fails_validation_with_duplicate_slug(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Organization::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Wayne Enterprises',
            'slug' => 'wayne-ent',
            'code' => 'WAYNE-01',
        ]);

        $response = $this->postJson('/api/v1/organizations', [
            'name' => 'Wayne Enterprises Duplicate',
            'slug' => 'wayne-ent',
            'code' => 'WAYNE-02',
        ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_list_organizations(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Organization::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Org One',
            'slug' => 'org-one',
            'code' => 'ORG-1',
        ]);

        $response = $this->getJson('/api/v1/organizations');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Organizations retrieved successfully',
            ]);

        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    public function test_authenticated_user_can_show_organization_by_uuid(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $uuid = (string) Str::uuid();
        Organization::create([
            'uuid' => $uuid,
            'name' => 'Cyberdyne Systems',
            'slug' => 'cyberdyne-sys',
            'code' => 'CYBER-01',
        ]);

        $response = $this->getJson("/api/v1/organizations/{$uuid}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Organization details retrieved successfully',
                'data' => [
                    'uuid' => $uuid,
                    'name' => 'Cyberdyne Systems',
                ],
            ]);
    }

    public function test_show_organization_with_invalid_uuid_returns_404(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $invalidUuid = (string) Str::uuid();

        $response = $this->getJson("/api/v1/organizations/{$invalidUuid}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_authenticated_user_can_create_department_by_organization_uuid(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $uuid = (string) Str::uuid();
        Organization::create([
            'uuid' => $uuid,
            'name' => 'Aperture Science',
            'slug' => 'aperture-sci',
            'code' => 'APERTURE-1',
        ]);

        $response = $this->postJson("/api/v1/organizations/{$uuid}/departments", [
            'name' => 'Testing Dept',
            'code' => 'TEST-DEPT',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Department created successfully',
                'data' => [
                    'name' => 'Testing Dept',
                    'code' => 'TEST-DEPT',
                ],
            ]);
    }

    public function test_authenticated_user_can_assign_membership_by_organization_uuid(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $uuid = (string) Str::uuid();
        Organization::create([
            'uuid' => $uuid,
            'name' => 'Umbrella Corp',
            'slug' => 'umbrella-corp',
            'code' => 'UMB-01',
        ]);

        $response = $this->postJson("/api/v1/organizations/{$uuid}/memberships", [
            'user_id' => $user->id,
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User assigned to organization successfully',
                'data' => [
                    'user_id' => $user->id,
                    'role' => 'admin',
                ],
            ]);
    }

    public function test_guest_cannot_create_organization(): void
    {
        $response = $this->postJson('/api/v1/organizations', [
            'name' => 'Guest Corp',
            'slug' => 'guest-corp',
            'code' => 'GUEST-01',
        ]);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_list_organizations(): void
    {
        $response = $this->getJson('/api/v1/organizations');

        $response->assertStatus(401);
    }

    public function test_guest_cannot_show_organization(): void
    {
        $uuid = (string) Str::uuid();
        $response = $this->getJson("/api/v1/organizations/{$uuid}");

        $response->assertStatus(401);
    }

    public function test_guest_cannot_create_department(): void
    {
        $uuid = (string) Str::uuid();
        $response = $this->postJson("/api/v1/organizations/{$uuid}/departments", [
            'name' => 'Guest Dept',
            'code' => 'GUEST-D',
        ]);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_assign_membership(): void
    {
        $uuid = (string) Str::uuid();
        $response = $this->postJson("/api/v1/organizations/{$uuid}/memberships", [
            'user_id' => 1,
        ]);

        $response->assertStatus(401);
    }
}
