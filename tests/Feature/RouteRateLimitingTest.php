<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteRateLimitingTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        return User::create([
            'name' => 'Staff',
            'email' => 'staff@example.com',
            'password' => bcrypt('secret'),
            'role' => 'staff',
            'is_active' => true,
        ]);
    }

    public function test_login_is_throttled_after_five_attempts_per_minute(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'nobody@example.com',
                'password' => 'wrong-password',
            ])->assertStatus(302);
        }

        $this->post('/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_stock_transfer_submissions_are_throttled_after_ten_per_minute(): void
    {
        $user = $this->createUser();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->post('/transfers/store', [])->assertStatus(302);
        }

        $this->actingAs($user)->post('/transfers/store', [])->assertStatus(429);
    }

    public function test_pos_checkout_is_throttled_per_user(): void
    {
        $user = $this->createUser();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->postJson('/pos/checkout', [])->assertStatus(400);
        }

        $this->actingAs($user)->postJson('/pos/checkout', [])
            ->assertStatus(429)
            ->assertJson([
                'error' => 'Too many checkout attempts. Please wait a moment and try again.',
            ]);
    }
}
