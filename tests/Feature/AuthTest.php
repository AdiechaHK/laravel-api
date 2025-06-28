<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_get_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'user'
            ]);

        $token = $response->json('access_token');
        $this->assertNotEmpty($token);
    }

    public function test_protected_route_requires_authentication(): void
    {
        $response = $this->getJson('/api/posts');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_access_protected_route(): void
    {
        $user = User::factory()->create();
        
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/posts');

        $response->assertStatus(200);
    }
} 