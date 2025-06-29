<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    /**
     * Test user registration with valid data
     */
    public function test_user_can_register_with_valid_data()
    {
        $userData = [
            'name' => 'Vinith Kumar',
            'email' => 'Vinith@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->post('/api/register', $userData);

        $response->assertResponseStatus(201);
        $response->seeJson([
            'success' => true,
            'message' => 'User registered successfully'
        ]);
        $response->seeJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'name',
                    'email'
                ],
                'token'
            ]
        ]);
    }

    /**
     * Test user registration with invalid email
     */
    public function test_user_cannot_register_with_invalid_email()
    {
        $userData = [
            'name' => 'Vinith Kumar',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->post('/api/register', $userData);

        $response->assertResponseStatus(422);
        $response->seeJsonStructure([
            'success',
            'message',
            'errors' => [
                'email'
            ]
        ]);
    }

    /**
     * Test user registration with duplicate email
     */
    public function test_user_cannot_register_with_duplicate_email()
    {
        // Create first user
        User::create([
            'name' => 'Vinith Kumar',
            'email' => 'Vinith@example.com',
            'password' => Hash::make('password123')
        ]);

        // Try to register with same email
        $userData = [
            'name' => 'Jane Kumar',
            'email' => 'Vinith@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->post('/api/register', $userData);

        $response->assertResponseStatus(422);
        $response->seeJsonStructure([
            'success',
            'message',
            'errors' => [
                'email'
            ]
        ]);
    }

    /**
     * Test user registration with short password
     */
    public function test_user_cannot_register_with_short_password()
    {
        $userData = [
            'name' => 'Vinith Kumar',
            'email' => 'Vinith@example.com',
            'password' => '123',
            'password_confirmation' => '123'
        ];

        $response = $this->post('/api/register', $userData);

        $response->assertResponseStatus(422);
        $response->seeJsonStructure([
            'success',
            'message',
            'errors' => [
                'password'
            ]
        ]);
    }

    /**
     * Test user registration with mismatched passwords
     */
    public function test_user_cannot_register_with_mismatched_passwords()
    {
        $userData = [
            'name' => 'Vinith Kumar',
            'email' => 'Vinith@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword'
        ];

        $response = $this->post('/api/register', $userData);

        $response->assertResponseStatus(422);
        $response->seeJsonStructure([
            'success',
            'message',
            'errors' => [
                'password'
            ]
        ]);
    }

    /**
     * Test user login with valid credentials
     */
    public function test_user_can_login_with_valid_credentials()
    {
        // Create user
        User::create([
            'name' => 'Vinith Kumar',
            'email' => 'Vinith@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => 'Vinith@example.com',
            'password' => 'password123'
        ];

        $response = $this->post('/api/login', $loginData);

        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'Login successful'
        ]);
        $response->seeJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email'
                ],
                'token'
            ]
        ]);
    }

    /**
     * Test user login with invalid credentials
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        // Create user
        User::create([
            'name' => 'Vinith Kumar',
            'email' => 'Vinith@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginData = [
            'email' => 'Vinith@example.com',
            'password' => 'wrongpassword'
        ];

        $response = $this->post('/api/login', $loginData);

        $response->assertResponseStatus(401);
        $response->seeJson([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
    }

    /**
     * Test user login with non-existent email
     */
    public function test_user_cannot_login_with_nonexistent_email()
    {
        $loginData = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ];

        $response = $this->post('/api/login', $loginData);

        $response->assertResponseStatus(401);
        $response->seeJson([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
    }

    /**
     * Test user profile retrieval with valid token
     */
    public function test_user_can_get_profile_with_valid_token()
    {
        // Create and login user
        $user = User::create([
            'name' => 'Vinith Kumar',
            'email' => 'Vinith@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginResponse = $this->post('/api/login', [
            'email' => 'Vinith@example.com',
            'password' => 'password123'
        ]);

        $token = json_decode($loginResponse->response->getContent(), true)['data']['token'];

        $response = $this->get('/api/me', ['Authorization' => 'Bearer ' . $token]);

        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'User profile retrieved successfully'
        ]);
        $response->seeJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'name',
                'email'
            ]
        ]);
    }

    /**
     * Test user profile retrieval without token
     */
    public function test_user_cannot_get_profile_without_token()
    {
        $response = $this->get('/api/me');

        $response->assertResponseStatus(401);
    }

    /**
     * Test token refresh
     */
    public function test_user_can_refresh_token()
    {
        // Create and login user
        $user = User::create([
            'name' => 'Vinith Kumar',
            'email' => 'Vinith@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginResponse = $this->post('/api/login', [
            'email' => 'Vinith@example.com',
            'password' => 'password123'
        ]);

        $token = json_decode($loginResponse->response->getContent(), true)['data']['token'];

        $response = $this->post('/api/refresh', [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'Token refreshed successfully'
        ]);
        $response->seeJsonStructure([
            'success',
            'message',
            'data' => [
                'token'
            ]
        ]);
    }

    /**
     * Test logout
     */
    public function test_user_can_logout()
    {
        // Create and login user
        $user = User::create([
            'name' => 'Vinith Kumar',
            'email' => 'Vinith@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginResponse = $this->post('/api/login', [
            'email' => 'Vinith@example.com',
            'password' => 'password123'
        ]);

        $token = json_decode($loginResponse->response->getContent(), true)['data']['token'];

        $response = $this->post('/api/logout', [], ['Authorization' => 'Bearer ' . $token]);

        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    /**
     * Test protected route after logout
     */
    public function test_protected_route_returns_401_after_logout()
    {
        // Create and login user
        $user = User::create([
            'name' => 'Vinith Kumar',
            'email' => 'Vinith@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginResponse = $this->post('/api/login', [
            'email' => 'Vinith@example.com',
            'password' => 'password123'
        ]);

        $token = json_decode($loginResponse->response->getContent(), true)['data']['token'];

        // Logout
        $this->post('/api/logout', [], ['Authorization' => 'Bearer ' . $token]);

        // Try to access protected route
        $response = $this->get('/api/me', ['Authorization' => 'Bearer ' . $token]);

        $response->assertResponseStatus(401);
    }
}
