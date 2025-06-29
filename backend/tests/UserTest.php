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
        
        // Clean up database before each test
        \App\Models\User::truncate();
        \App\Models\Task::truncate();
        \App\Models\ApiLog::truncate();
        
        // Create a user and get JWT token
        $this->user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $loginResponse = $this->post('/api/login', [
            'email' => 'admin@example.com',
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
            'name' => 'Vinith Kumar',
            'email' => 'vinith@example.com',
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
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'from',
                    'to'
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
            'name' => 'Vinith Kumar',
            'email' => 'vinith@example.com',
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
            'email' => 'vinith@example.com',
            'password' => Hash::make('password123')
        ]);

        // Try to create second user with same email
        $userData = [
            'name' => 'Vinith Kumar',
            'email' => 'vinith@example.com',
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
            'name' => 'Vinith Kumar',
            'email' => 'vinith@example.com',
            'password' => Hash::make('password123')
        ]);

        $updateData = [
            'name' => 'Vinith Updated Kumar',
            'email' => 'vinithupdated@example.com'
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
            'name' => 'Vinith Kumar',
            'email' => 'vinith@example.com',
            'password' => Hash::make('password123')
        ]);

        $updateData = [
            'name' => 'Vinith Updated Kumar',
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
        // Create two users with different emails
        $user1 = User::create([
            'name' => 'Vinith Kumar',
            'email' => 'vinith@example.com',
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
            'email' => 'vinith@example.com'
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
            'name' => 'Vinith Kumar',
            'email' => 'vinith@example.com',
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

    /**
     * Test filtering users by search
     */
    public function test_can_filter_users_by_search()
    {
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123')
        ]);

        User::create([
            'name' => 'Vinith Kumar',
            'email' => 'vinith@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->get('/api/users?search=john', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'Users retrieved successfully'
        ]);

        $data = json_decode($response->response->getContent(), true);
        $this->assertEquals(1, $data['data']['pagination']['total']);
        $this->assertStringContainsString('john', strtolower($data['data']['data'][0]['name']));
    }

    /**
     * Test filtering users by active status
     */
    public function test_can_filter_users_by_active_status()
    {
        User::create([
            'name' => 'Active User',
            'email' => 'active@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true
        ]);

        User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false
        ]);

        $response = $this->get('/api/users?is_active=1', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $data = json_decode($response->response->getContent(), true);
        $this->assertGreaterThan(0, $data['data']['pagination']['total']);
    }

    /**
     * Test sorting users
     */
    public function test_can_sort_users()
    {
        User::create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => Hash::make('password123')
        ]);

        User::create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->get('/api/users?sort_by=name&sort_order=asc', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $data = json_decode($response->response->getContent(), true);
        // Admin User comes first alphabetically, then Alice, then Bob
        $this->assertEquals('Admin User', $data['data']['data'][0]['name']);
        $this->assertEquals('Alice', $data['data']['data'][1]['name']);
    }

    /**
     * Test pagination
     */
    public function test_can_paginate_users()
    {
        // Create multiple users
        for ($i = 1; $i <= 25; $i++) {
            User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password123')
            ]);
        }

        $response = $this->get('/api/users?per_page=10', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $data = json_decode($response->response->getContent(), true);
        $this->assertEquals(10, $data['data']['pagination']['per_page']);
        $this->assertEquals(10, count($data['data']['data']));
        $this->assertGreaterThan(1, $data['data']['pagination']['last_page']);
    }

    /**
     * Test validation of filter parameters
     */
    public function test_validates_filter_parameters()
    {
        $response = $this->get('/api/users?sort_by=invalid_field', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(422);
    }

    /**
     * Test validation of pagination parameters
     */
    public function test_validates_pagination_parameters()
    {
        $response = $this->get('/api/users?per_page=150', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(422);
    }

    /**
     * Test filtering users by role
     */
    public function test_can_filter_users_by_role()
    {
        User::create([
            'name' => 'Test Admin User',
            'email' => 'testadmin2@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin'
        ]);

        User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user'
        ]);

        $response = $this->get('/api/users?role=admin', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $data = json_decode($response->response->getContent(), true);
        $this->assertCount(2, $data['data']['data']); // Admin User from setUp + Test Admin User
        $this->assertEquals('admin', $data['data']['data'][0]['role']);
    }

    /**
     * Test filtering users by date range
     */
    public function test_can_filter_users_by_date_range()
    {
        $user = User::create([
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => Hash::make('password123'),
        ]);
        $user->created_at = '2025-06-15 10:00:00';
        $user->save();

        $response = $this->get('/api/users?start_date=2025-06-01&end_date=2025-06-30', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $data = json_decode($response->response->getContent(), true);
        $this->assertGreaterThan(0, $data['data']['pagination']['total']);
    }

    /**
     * Test response includes pagination metadata
     */
    public function test_response_includes_pagination_metadata()
    {
        User::create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->get('/api/users', ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(200);

        $data = json_decode($response->response->getContent(), true);
        $this->assertArrayHasKey('pagination', $data['data']);
        $this->assertArrayHasKey('current_page', $data['data']['pagination']);
        $this->assertArrayHasKey('per_page', $data['data']['pagination']);
        $this->assertArrayHasKey('total', $data['data']['pagination']);
    }

    /**
     * Test response includes applied filters
     */
    public function test_response_includes_applied_filters()
    {
        $response = $this->get('/api/users?search=test&is_active=true&sort_by=name&sort_order=asc', ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(200);

        $data = json_decode($response->response->getContent(), true);
        $this->assertArrayHasKey('filters', $data['data']);
        $this->assertEquals('test', $data['data']['filters']['search']);
        $this->assertEquals('true', $data['data']['filters']['is_active']);
        $this->assertEquals('name', $data['data']['filters']['sort_by']);
        $this->assertEquals('asc', $data['data']['filters']['sort_order']);
    }

    /**
     * Test validation of role filter parameter
     */
    public function test_validates_role_filter_parameter()
    {
        $response = $this->get('/api/users?role=invalid_role', ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(422);
        $response->seeJsonStructure(['errors' => ['role']]);
    }

    /**
     * Test validation of date range parameters
     */
    public function test_validates_date_range_parameters()
    {
        $response = $this->get('/api/users?start_date=2025-06-30&end_date=2025-06-01', ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(422);
        $response->seeJsonStructure(['errors' => ['end_date']]);
    }
}
