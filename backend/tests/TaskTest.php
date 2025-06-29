<?php

namespace Tests;

use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Hash;

class TaskTest extends TestCase
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

    public function test_can_get_all_tasks()
    {
        Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Test Task 1',
            'description' => 'Test Description 1',
            'status' => 'pending',
            'priority' => 'medium'
        ]);
        Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Test Task 2',
            'description' => 'Test Description 2',
            'status' => 'in_progress',
            'priority' => 'high'
        ]);
        $response = $this->get('/api/tasks', ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'Tasks retrieved successfully'
        ]);
    }

    public function test_cannot_get_tasks_without_authentication()
    {
        $response = $this->get('/api/tasks', ['Accept' => 'application/json']);
        $response->assertResponseStatus(401);
        $this->assertStringContainsString('Unauthorized', $response->response->getContent());
    }

    public function test_can_create_new_task()
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'New Task Description',
            'status' => 'pending',
            'priority' => 'medium',
            'due_date' => '2025-12-31'
        ];
        $response = $this->post('/api/tasks', $taskData, ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(201);
        $response->seeJson([
            'success' => true,
            'message' => 'Task created successfully'
        ]);
    }

    public function test_cannot_create_task_without_title()
    {
        $taskData = [
            'description' => 'New Task Description',
            'status' => 'pending',
            'priority' => 'medium'
        ];
        $response = $this->post('/api/tasks', $taskData, ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(422);
        $response->seeJsonStructure(['errors' => ['title']]);
    }

    public function test_cannot_create_task_with_invalid_status()
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'New Task Description',
            'status' => 'invalid_status',
            'priority' => 'medium'
        ];
        $response = $this->post('/api/tasks', $taskData, ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(422);
        $response->seeJsonStructure(['errors' => ['status']]);
    }

    public function test_cannot_create_task_with_invalid_priority()
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'New Task Description',
            'status' => 'pending',
            'priority' => 'invalid_priority'
        ];
        $response = $this->post('/api/tasks', $taskData, ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(422);
        $response->seeJsonStructure(['errors' => ['priority']]);
    }

    public function test_cannot_create_task_with_past_due_date()
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'New Task Description',
            'status' => 'pending',
            'priority' => 'medium',
            'due_date' => '2020-01-01'
        ];
        $response = $this->post('/api/tasks', $taskData, ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(422);
        $response->seeJsonStructure(['errors' => ['due_date']]);
    }

    public function test_can_get_specific_task()
    {
        $task = Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'priority' => 'medium'
        ]);
        $response = $this->get('/api/tasks/' . $task->id, ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'Task retrieved successfully'
        ]);
    }

    public function test_cannot_get_nonexistent_task()
    {
        $response = $this->get('/api/tasks/999', ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(404);
        $response->seeJson(['success' => false, 'message' => 'Task not found']);
    }

    public function test_cannot_get_task_belonging_to_another_user()
    {
        $otherUser = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123')
        ]);
        $task = Task::create([
            'user_id' => $otherUser->id,
            'assigned_by' => $otherUser->id,
            'title' => 'Other User Task',
            'description' => 'Other User Description',
            'status' => 'pending',
            'priority' => 'medium'
        ]);
        $response = $this->get('/api/tasks/' . $task->id, ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(404);
        $response->seeJson(['success' => false, 'message' => 'Task not found']);
    }

    public function test_can_update_task()
    {
        $task = Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'priority' => 'medium'
        ]);
        $updateData = [
            'title' => 'Updated Task',
            'description' => 'Updated Description',
            'status' => 'in_progress',
            'priority' => 'high'
        ];
        $response = $this->put('/api/tasks/' . $task->id, $updateData, ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'Task updated successfully'
        ]);
    }

    public function test_can_update_task_to_completed()
    {
        $task = Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'priority' => 'medium'
        ]);
        $updateData = [
            'status' => 'completed'
        ];
        $response = $this->put('/api/tasks/' . $task->id, $updateData, ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'Task updated successfully'
        ]);
    }

    public function test_cannot_update_nonexistent_task()
    {
        $updateData = [
            'title' => 'Updated Task',
            'description' => 'Updated Description'
        ];
        $response = $this->put('/api/tasks/999', $updateData, ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(404);
        $response->seeJson(['success' => false, 'message' => 'Task not found']);
    }

    public function test_cannot_update_task_with_invalid_status()
    {
        $task = Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'priority' => 'medium'
        ]);
        $updateData = [
            'status' => 'invalid_status'
        ];
        $response = $this->put('/api/tasks/' . $task->id, $updateData, ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(422);
        $response->seeJsonStructure(['errors' => ['status']]);
    }

    public function test_can_update_task_status_via_patch()
    {
        $task = Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'priority' => 'medium'
        ]);
        $updateData = [
            'status' => 'completed'
        ];
        $response = $this->patch('/api/tasks/' . $task->id . '/status', $updateData, ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'Task status updated successfully'
        ]);
    }

    public function test_cannot_update_task_status_without_status_field()
    {
        $task = Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'priority' => 'medium'
        ]);
        $response = $this->patch('/api/tasks/' . $task->id . '/status', [], ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(422);
        $response->seeJsonStructure(['errors' => ['status']]);
    }

    public function test_can_delete_task()
    {
        $task = Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'priority' => 'medium'
        ]);
        $response = $this->delete('/api/tasks/' . $task->id, [], ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);
    }

    public function test_cannot_delete_nonexistent_task()
    {
        $response = $this->delete('/api/tasks/999', [], ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(404);
        $response->seeJson(['success' => false, 'message' => 'Task not found']);
    }

    public function test_can_get_tasks_for_specific_user()
    {
        $otherUser = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123')
        ]);
        Task::create([
            'user_id' => $otherUser->id,
            'assigned_by' => $otherUser->id,
            'title' => 'Other User Task',
            'description' => 'Other User Description',
            'status' => 'pending',
            'priority' => 'medium'
        ]);
        $response = $this->get('/api/users/' . $otherUser->id . '/tasks', ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'User tasks retrieved successfully'
        ]);
    }

    public function test_cannot_get_tasks_for_nonexistent_user()
    {
        $response = $this->get('/api/users/999/tasks', ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(404);
        $response->seeJson(['success' => false, 'message' => 'User not found']);
    }

    public function test_assigned_by_field_is_set_correctly()
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'New Task Description',
            'status' => 'pending',
            'priority' => 'medium'
        ];
        $response = $this->post('/api/tasks', $taskData, ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(201);
        $responseData = json_decode($response->response->getContent(), true);
        $this->assertEquals($this->user->id, $responseData['data']['assigned_by']);
    }
}
