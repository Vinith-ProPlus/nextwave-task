<?php

namespace Tests;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');

        // Create and authenticate user
        $this->user = User::create([
            'name' => 'Auth User',
            'email' => 'authuser@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginResponse = $this->post('/api/login', [
            'email' => 'authuser@example.com',
            'password' => 'password123'
        ]);

        $this->token = json_decode($loginResponse->response->getContent(), true)['data']['token'];
    }

    /**
     * Test getting all users
     */
    public function test_can_get_all_users()
    {
        // Create test users
        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123')
        ]);

        User::create([
            'name' => 'Bob Wilson',
            'email' => 'bob@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->get('/api/users', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'Users retrieved successfully'
        ]);
        $response->seeJsonStructure([
            'success',
            'message',
            'data' => [
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]
        ]);
    }

    /**
     * Test getting a specific user
     */
    public function test_can_get_specific_user()
    {
        $user = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->get('/api/users/' . $user->id, ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'User retrieved successfully'
        ]);
        $response->seeJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'name',
                'email',
                'created_at',
                'updated_at'
            ]
        ]);
    }

    /**
     * Test getting non-existent user
     */
    public function test_cannot_get_nonexistent_user()
    {
        $response = $this->get('/api/users/999', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(404);
        $response->seeJson([
            'success' => false,
            'message' => 'User not found'
        ]);
    }

    /**
     * Test creating a new user
     */
    public function test_can_create_new_user()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123'
        ];

        $response = $this->post('/api/users', $userData, ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(201);
        $response->seeJson([
            'success' => true,
            'message' => 'User created successfully'
        ]);
        $response->seeJsonStructure([
            'success',
            'message',
            'data' => [
                'name',
                'email',
                'created_at',
                'updated_at'
            ]
        ]);
    }

    /**
     * Test creating user with invalid email
     */
    public function test_cannot_create_user_with_invalid_email()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'invalid-email',
            'password' => 'password123'
        ];

        $response = $this->post('/api/users', $userData, ['Authorization' => 'Bearer ' . $this->token]);

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
     * Test creating user with duplicate email
     */
    public function test_cannot_create_user_with_duplicate_email()
    {
        // Create first user
        User::create([
            'name' => 'Vinith Kumar',
            'email' => 'Vinith@example.com',
            'password' => Hash::make('password123')
        ]);

        // Try to create second user with same email
        $userData = [
            'name' => 'Jane Kumar',
            'email' => 'Vinith@example.com',
            'password' => 'password123'
        ];

        $response = $this->post('/api/users', $userData, ['Authorization' => 'Bearer ' . $this->token]);

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
     * Test creating user with short password
     */
    public function test_cannot_create_user_with_short_password()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => '123'
        ];

        $response = $this->post('/api/users', $userData, ['Authorization' => 'Bearer ' . $this->token]);

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
     * Test creating user without required fields
     */
    public function test_cannot_create_user_without_required_fields()
    {
        $userData = [
            'email' => 'newuser@example.com'
        ];

        $response = $this->post('/api/users', $userData, ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(422);
        $response->seeJsonStructure([
            'success',
            'message',
            'errors' => [
                'name',
                'password'
            ]
        ]);
    }

    /**
     * Test updating a user
     */
    public function test_can_update_user()
    {
        $user = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123')
        ]);

        $updateData = [
            'name' => 'Jane Updated Smith',
            'email' => 'janeupdated@example.com'
        ];

        $response = $this->put('/api/users/' . $user->id, $updateData, ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
        $response->seeJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'name',
                'email',
                'created_at',
                'updated_at'
            ]
        ]);
    }

    /**
     * Test updating non-existent user
     */
    public function test_cannot_update_nonexistent_user()
    {
        $updateData = [
            'name' => 'Vinith Updated Kumar',
            'email' => 'Vinithupdated@example.com'
        ];

        $response = $this->put('/api/users/999', $updateData, ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(404);
        $response->seeJson([
            'success' => false,
            'message' => 'User not found'
        ]);
    }

    /**
     * Test updating user with invalid email
     */
    public function test_cannot_update_user_with_invalid_email()
    {
        $user = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123')
        ]);

        $updateData = [
            'name' => 'Jane Updated Smith',
            'email' => 'invalid-email'
        ];

        $response = $this->put('/api/users/' . $user->id, $updateData, ['Authorization' => 'Bearer ' . $this->token]);

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
     * Test updating user with duplicate email
     */
    public function test_cannot_update_user_with_duplicate_email()
    {
        // Create two users
        $user1 = User::create([
            'name' => 'Vinith Kumar',
            'email' => 'Vinith@example.com',
            'password' => Hash::make('password123')
        ]);

        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123')
        ]);

        // Try to update user2 with user1's email
        $updateData = [
            'name' => 'Jane Updated',
            'email' => 'Vinith@example.com'
        ];

        $response = $this->put('/api/users/' . $user2->id, $updateData, ['Authorization' => 'Bearer ' . $this->token]);

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
     * Test deleting a user
     */
    public function test_can_delete_user()
    {
        $user = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->delete('/api/users/' . $user->id, [], ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Test deleting non-existent user
     */
    public function test_cannot_delete_nonexistent_user()
    {
        $response = $this->delete('/api/users/999', [], ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(404);
        $response->seeJson([
            'success' => false,
            'message' => 'User not found'
        ]);
    }
}
