<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SanctumTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_generate_sanctum_api_token(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token');

        $this->assertNotNull($token->plainTextToken);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'test-token',
        ]);
    }

    public function test_user_can_revoke_sanctum_api_token(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('auth-token');
        $this->assertCount(1, $user->tokens);

        $user->tokens()->delete();

        $this->assertCount(0, $user->fresh()->tokens);
    }
}
