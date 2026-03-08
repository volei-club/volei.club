<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_correct_credentials_and_receive_2fa_code()
    {
        Mail::fake();

        $user = User::create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'role' => 'administrator',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'demo@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
            'status' => 'success',
            'message' => __('auth.2fa_sent'),
            'user_id' => $user->id
        ]);

        $user->refresh();
        $this->assertNotNull($user->two_factor_code);
        $this->assertNotNull($user->two_factor_expires_at);

        Mail::assertSent(\App\Mail\TwoFactorCodeMail::class);
    }

    public function test_user_cannot_login_with_incorrect_credentials()
    {
        User::create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'role' => 'administrator',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'demo@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
            'status' => 'error',
            'message' => __('auth.failed')
        ]);
    }

    public function test_user_can_verify_2fa_and_receive_token()
    {
        $user = User::create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'role' => 'administrator',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'two_factor_code' => '123456',
            'two_factor_expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/2fa/verify', [
            'user_id' => $user->id,
            'code' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
            'status',
            'message',
            'token',
            'user'
        ]);

        $user->refresh();
        $this->assertNull($user->two_factor_code);
        $this->assertNull($user->two_factor_expires_at);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class ,
        ]);

        // Also check audit log
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => User::class ,
            'auditable_id' => $user->id,
            'event' => 'logged_in',
        ]);
    }

    public function test_user_cannot_verify_2fa_with_wrong_code_or_expired()
    {
        $user = User::create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'role' => 'administrator',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'two_factor_code' => '123456',
            'two_factor_expires_at' => now()->subMinutes(1), // Expired
        ]);

        $response = $this->postJson('/api/2fa/verify', [
            'user_id' => $user->id,
            'code' => '123456',
        ]);

        $response->assertStatus(401);

        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        $response2 = $this->postJson('/api/2fa/verify', [
            'user_id' => $user->id,
            'code' => '654321', // Wrong code
        ]);

        $response2->assertStatus(401);
    }

    public function test_user_can_resend_2fa()
    {
        Mail::fake();

        $user = User::create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'role' => 'administrator',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/2fa/resend', [
            'user_id' => $user->id,
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertNotNull($user->two_factor_code);
        $this->assertNotNull($user->two_factor_expires_at);

        Mail::assertSent(\App\Mail\TwoFactorCodeMail::class);
    }
}
