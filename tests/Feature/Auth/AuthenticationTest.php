<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_authenticate_via_api()
    {
        $user = User::factory()->create([
            'email' => 'test@volei.club',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@volei.club',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token', 'token_type']);
        $this->assertEquals('Bearer', $response['token_type']);
    }

    public function test_user_cannot_authenticate_with_invalid_password()
    {
        $user = User::factory()->create([
            'email' => 'test@volei.club',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@volei.club',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }
}
