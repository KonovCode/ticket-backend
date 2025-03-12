<?php

namespace Tests\Feature;

use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_registration_correct_input(): void
    {
        $data = [
            'name' => 'Vladislav',
            'email' => 'vlad@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/api/register', $data);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'token',
        ]);
    }

    public function test_login_correct_input()
    {
        UserFactory::new()->create([
            'name' => 'Vladislav',
            'email' => 'vlad@example.com',
            'password' => Hash::make('password123'),
        ]);

        $data = [
            'email' => 'vlad@example.com',
            'password' => 'password123',
        ];

        $response = $this->post('/api/login', $data);

        $response->assertStatus(200);

        $response->assertJsonStructure(['token']);
    }

    public function test_logout_success()
    {
        $user = UserFactory::new()->create([
            'name' => 'Vladislav',
            'email' => 'vlad@example.com',
            'password' => Hash::make('password123'),
        ]);

        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out']);

        $this->assertCount(0, $user->tokens);
    }
}
