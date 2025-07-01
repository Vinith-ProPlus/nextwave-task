<?php

namespace Tests;

use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

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

    public function test_can_get_task_belonging_to_another_user()
    {
        $otherUser = User::create([
            'name' => 'Vinith Kumar',
            'email' => 'vinith@example.com',
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
        $response->assertResponseStatus(200);
        $response->seeJson(['success' => true, 'message' => 'Task retrieved successfully']);
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

        // Verify completed_at is set
        $task->refresh();
        $this->assertNotNull($task->completed_at);
    }

    public function test_cannot_update_nonexistent_task()
    {
        $updateData = [
            'title' => 'Updated Task',
            'status' => 'in_progress'
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

        // Verify completed_at is set
        $task->refresh();
        $this->assertNotNull($task->completed_at);
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
        $updateData = [];
        $response = $this->patch('/api/tasks/' . $task->id . '/status', $updateData, ['Authorization' => 'Bearer ' . $this->token]);
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

        // Verify task is deleted
        $this->assertNull(Task::find($task->id));
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
            'name' => 'Vinith Kumar',
            'email' => 'Vinith@example.com',
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

        $taskId = json_decode($response->response->getContent(), true)['data']['id'];
        $task = Task::find($taskId);
        $this->assertEquals($this->user->id, $task->assigned_by);
    }

    /**
     * Test filtering tasks by search
     */
    public function test_can_filter_tasks_by_search()
    {
        Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Important Task',
            'description' => 'This is an important task',
            'status' => 'pending',
            'priority' => 'high'
        ]);

        Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Regular Task',
            'description' => 'This is a regular task',
            'status' => 'pending',
            'priority' => 'medium'
        ]);

        $response = $this->get('/api/tasks?search=important', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $data = json_decode($response->response->getContent(), true);
        $this->assertEquals(1, $data['data']['pagination']['total']);
        $this->assertStringContainsString('important', strtolower($data['data']['data'][0]['title']));
    }

    /**
     * Test filtering tasks by status
     */
    public function test_can_filter_tasks_by_status()
    {
        Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Pending Task',
            'description' => 'This is a pending task',
            'status' => 'pending',
            'priority' => 'medium'
        ]);

        Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Completed Task',
            'description' => 'This is a completed task',
            'status' => 'completed',
            'priority' => 'medium'
        ]);

        $response = $this->get('/api/tasks?status=completed', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $data = json_decode($response->response->getContent(), true);
        $this->assertEquals(1, $data['data']['pagination']['total']);
        $this->assertEquals('completed', $data['data']['data'][0]['status']);
    }

    /**
     * Test filtering tasks by priority
     */
    public function test_can_filter_tasks_by_priority()
    {
        Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'High Priority Task',
            'description' => 'This is a high priority task',
            'status' => 'pending',
            'priority' => 'high'
        ]);

        Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Low Priority Task',
            'description' => 'This is a low priority task',
            'status' => 'pending',
            'priority' => 'low'
        ]);

        $response = $this->get('/api/tasks?priority=high', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $data = json_decode($response->response->getContent(), true);
        $this->assertEquals(1, $data['data']['pagination']['total']);
        $this->assertEquals('high', $data['data']['data'][0]['priority']);
    }

    /**
     * Test filtering tasks by completion status
     */
    public function test_can_filter_tasks_by_is_completed()
    {
        $completedTask = Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Completed Task',
            'description' => 'This is a completed task',
            'status' => 'completed',
            'priority' => 'medium',
            'completed_at' => Carbon::now()
        ]);

        Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Pending Task',
            'description' => 'This is a pending task',
            'status' => 'pending',
            'priority' => 'medium'
        ]);

        $response = $this->get('/api/tasks?is_completed=true', ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(200);

        $data = json_decode($response->response->getContent(), true);
        $this->assertCount(1, $data['data']['data']);
        $this->assertEquals('completed', $data['data']['data'][0]['status']);
    }

    /**
     * Test filtering tasks by overdue status
     */
    public function test_can_filter_tasks_by_is_overdue()
    {
        Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Overdue Task',
            'description' => 'This is an overdue task',
            'status' => 'pending',
            'priority' => 'medium',
            'due_date' => Carbon::now()->subDays(5)
        ]);

        Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Future Task',
            'description' => 'This is a future task',
            'status' => 'pending',
            'priority' => 'medium',
            'due_date' => Carbon::now()->addDays(5)
        ]);

        $response = $this->get('/api/tasks?is_overdue=true', ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(200);

        $data = json_decode($response->response->getContent(), true);
        $this->assertCount(1, $data['data']['data']);
        $this->assertEquals('Overdue Task', $data['data']['data'][0]['title']);
    }

    /**
     * Test sorting tasks
     */
    public function test_can_sort_tasks()
    {
        Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Alpha Task',
            'description' => 'First task',
            'status' => 'pending',
            'priority' => 'medium'
        ]);

        Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Beta Task',
            'description' => 'Second task',
            'status' => 'pending',
            'priority' => 'medium'
        ]);

        $response = $this->get('/api/tasks?sort_by=title&sort_order=asc', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $data = json_decode($response->response->getContent(), true);
        $this->assertEquals('Alpha Task', $data['data']['data'][0]['title']);
    }

    /**
     * Test pagination
     */
    public function test_can_paginate_tasks()
    {
        // Create multiple tasks
        for ($i = 1; $i <= 25; $i++) {
            Task::create([
                'user_id' => $this->user->id,
                'assigned_by' => $this->user->id,
                'title' => "Task {$i}",
                'description' => "Description for task {$i}",
                'status' => 'pending',
                'priority' => 'medium'
            ]);
        }

        $response = $this->get('/api/tasks?per_page=10', ['Authorization' => 'Bearer ' . $this->token]);

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
        $response = $this->get('/api/tasks?sort_by=invalid_field', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(422);
    }

    /**
     * Test response includes pagination metadata
     */
    public function test_response_includes_pagination_metadata()
    {
        Task::create([
            'user_id' => $this->user->id,
            'assigned_by' => $this->user->id,
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'priority' => 'medium'
        ]);

        $response = $this->get('/api/tasks', ['Authorization' => 'Bearer ' . $this->token]);
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
        $response = $this->get('/api/tasks?search=test&status=pending&sort_by=title&sort_order=asc', ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(200);

        $data = json_decode($response->response->getContent(), true);
        $this->assertArrayHasKey('filters', $data['data']);
        $this->assertEquals('test', $data['data']['filters']['search']);
        $this->assertEquals('pending', $data['data']['filters']['status']);
        $this->assertEquals('title', $data['data']['filters']['sort_by']);
        $this->assertEquals('asc', $data['data']['filters']['sort_order']);
    }
}
