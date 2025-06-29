<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test user registration
     *
     * @return void
     */
    public function test_user_can_register()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/api/register', $userData);

        $response->assertResponseStatus(201);
        $response->seeJson([
            'success' => true,
            'message' => 'User registered successfully',
        ]);
        $response->seeJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'created_at',
                    'updated_at',
                ],
                'token',
            ],
        ]);
    }

    /**
     * Test user login
     *
     * @return void
     */
    public function test_user_can_login()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->post('/api/login', $loginData);

        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'Login successful',
        ]);
        $response->seeJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'created_at',
                    'updated_at',
                ],
                'token',
            ],
        ]);
    }

    /**
     * Test login with invalid credentials
     *
     * @return void
     */
    public function test_login_with_invalid_credentials()
    {
        $loginData = [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->post('/api/login', $loginData);

        $response->assertResponseStatus(401);
        $response->seeJson([
            'success' => false,
            'message' => 'Invalid credentials',
        ]);
    }
}
