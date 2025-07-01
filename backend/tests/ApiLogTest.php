<?php

namespace Tests;

use App\Models\User;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Hash;

class ApiLogTest extends TestCase
{
    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Models\ApiLog::truncate();
        // Create a user and get JWT token
        $this->user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123')
        ]);

        $loginResponse = $this->post('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password123'
        ]);

        $this->token = json_decode($loginResponse->response->getContent(), true)['data']['token'];
    }

    public function test_can_get_api_logs_with_pagination()
    {
        // Create some API logs
        ApiLog::create([
            'method' => 'GET',
            'endpoint' => '/api/users',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 150.5,
            'status_code' => 200,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        ApiLog::create([
            'method' => 'POST',
            'endpoint' => '/api/tasks',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 200.0,
            'status_code' => 201,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        $response = $this->get('/api/logs', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'API logs retrieved successfully'
        ]);
        $response->seeJsonStructure([
            'success',
            'message',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'method',
                        'endpoint',
                        'timestamp',
                        'duration_ms',
                        'status_code',
                        'user_agent',
                        'ip'
                    ]
                ],
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'from',
                    'to'
                ],
                'filters'
            ]
        ]);

        $data = json_decode($response->response->getContent(), true);
        $this->assertEquals(3, $data['data']['pagination']['total']); // 2 created + 1 from login request
    }

    public function test_can_filter_api_logs_by_method()
    {
        ApiLog::create([
            'method' => 'GET',
            'endpoint' => '/api/users',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 150.5,
            'status_code' => 200,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        ApiLog::create([
            'method' => 'POST',
            'endpoint' => '/api/tasks',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 200.0,
            'status_code' => 201,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        $response = $this->get('/api/logs?method=GET', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $data = json_decode($response->response->getContent(), true);
        $this->assertEquals(1, $data['data']['pagination']['total']);
        $this->assertEquals('GET', $data['data']['data'][0]['method']);
    }

    public function test_can_filter_api_logs_by_status_code()
    {
        ApiLog::create([
            'method' => 'GET',
            'endpoint' => '/api/users',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 150.5,
            'status_code' => 200,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        ApiLog::create([
            'method' => 'POST',
            'endpoint' => '/api/tasks',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 200.0,
            'status_code' => 422,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        $response = $this->get('/api/logs?status_code=422', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $data = json_decode($response->response->getContent(), true);
        $this->assertEquals(1, $data['data']['pagination']['total']);
        $this->assertEquals(422, $data['data']['data'][0]['status_code']);
    }

    public function test_can_search_api_logs()
    {
        ApiLog::create([
            'method' => 'GET',
            'endpoint' => '/api/users',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 150.5,
            'status_code' => 200,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        ApiLog::create([
            'method' => 'POST',
            'endpoint' => '/api/tasks',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 200.0,
            'status_code' => 201,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        $response = $this->get('/api/logs?search=users', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $data = json_decode($response->response->getContent(), true);
        $this->assertEquals(1, $data['data']['pagination']['total']);
        $this->assertStringContainsString('users', $data['data']['data'][0]['endpoint']);
    }

    public function test_can_sort_api_logs()
    {
        ApiLog::create([
            'method' => 'GET',
            'endpoint' => '/api/users',
            'timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'duration_ms' => 150.5,
            'status_code' => 200,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        ApiLog::create([
            'method' => 'POST',
            'endpoint' => '/api/tasks',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 200.0,
            'status_code' => 201,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        $response = $this->get('/api/logs?sort_by=timestamp&sort_order=asc', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $data = json_decode($response->response->getContent(), true);
        $this->assertEquals('GET', $data['data']['data'][0]['method']);
    }

    public function test_can_get_specific_api_log()
    {
        $log = ApiLog::create([
            'method' => 'GET',
            'endpoint' => '/api/users',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 150.5,
            'status_code' => 200,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        $response = $this->get('/api/logs/' . $log->id, ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $response->seeJson([
            'success' => true,
            'message' => 'API log retrieved successfully'
        ]);
        $response->seeJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'method',
                'endpoint',
                'timestamp',
                'duration_ms',
                'status_code',
                'user_agent',
                'ip'
            ]
        ]);
    }

    public function test_can_get_api_logs_statistics()
    {
        ApiLog::create([
            'method' => 'GET',
            'endpoint' => '/api/users',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 150.5,
            'status_code' => 200,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        ApiLog::create([
            'method' => 'POST',
            'endpoint' => '/api/tasks',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 200.0,
            'status_code' => 201,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        $response = $this->get('/api/logs/statistics', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $response->seeJsonStructure([
            'success',
            'message',
            'data' => [
                'total_requests',
                'requests_today',
                'requests_this_week',
                'requests_this_month',
                'methods',
                'status_codes',
                'average_response_time',
                'top_endpoints'
            ]
        ]);

        $data = json_decode($response->response->getContent(), true);
        $this->assertEquals(3, $data['data']['total_requests']); // 2 created + 1 from login request
    }

    public function test_returns_404_for_nonexistent_log()
    {
        $response = $this->get('/api/logs/999', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(404);
    }

    public function test_validates_filter_parameters()
    {
        $response = $this->get('/api/logs?method=INVALID', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(422);
    }

    public function test_validates_sort_parameters()
    {
        $response = $this->get('/api/logs?sort_by=invalid_field', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(422);
    }

    public function test_requires_authentication()
    {
        $response = $this->get('/api/logs');
        $response->assertResponseStatus(401);

        $response = $this->get('/api/logs/1');
        $response->assertResponseStatus(401);

        $response = $this->get('/api/logs/statistics');
        $response->assertResponseStatus(401);
    }

    /**
     * Test filtering API logs by status code range
     */
    public function test_can_filter_api_logs_by_status_code_range()
    {
        ApiLog::create([
            'method' => 'GET',
            'endpoint' => '/api/users',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 150.5,
            'status_code' => 200,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        ApiLog::create([
            'method' => 'POST',
            'endpoint' => '/api/tasks',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 200.0,
            'status_code' => 422,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        $response = $this->get('/api/logs?status_code_range=4xx', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $data = json_decode($response->response->getContent(), true);
        $this->assertCount(1, $data['data']['data']);
        $this->assertEquals(422, $data['data']['data'][0]['status_code']);
    }

    /**
     * Test filtering API logs by duration range
     */
    public function test_can_filter_api_logs_by_duration_range()
    {
        ApiLog::create([
            'method' => 'GET',
            'endpoint' => '/api/users',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 50.0,
            'status_code' => 200,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        ApiLog::create([
            'method' => 'POST',
            'endpoint' => '/api/tasks',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 300.0,
            'status_code' => 201,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        $response = $this->get('/api/logs?min_duration=100&max_duration=500', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $data = json_decode($response->response->getContent(), true);
        $filtered = array_filter($data['data']['data'], function($log) {
            return $log['endpoint'] === '/api/tasks';
        });
        $this->assertCount(1, $filtered);
        $this->assertEquals(300.0, array_values($filtered)[0]['duration_ms']);
    }

    /**
     * Test filtering API logs by minimum duration only
     */
    public function test_can_filter_api_logs_by_min_duration_only()
    {
        ApiLog::create([
            'method' => 'GET',
            'endpoint' => '/api/users',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 50.0,
            'status_code' => 200,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        ApiLog::create([
            'method' => 'POST',
            'endpoint' => '/api/tasks',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 300.0,
            'status_code' => 201,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        $response = $this->get('/api/logs?min_duration=100', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $data = json_decode($response->response->getContent(), true);
        $filtered = array_filter($data['data']['data'], function($log) {
            return $log['endpoint'] === '/api/tasks';
        });
        $this->assertCount(1, $filtered);
        $this->assertEquals(300.0, array_values($filtered)[0]['duration_ms']);
    }

    /**
     * Test filtering API logs by maximum duration only
     */
    public function test_can_filter_api_logs_by_max_duration_only()
    {
        ApiLog::create([
            'method' => 'GET',
            'endpoint' => '/api/users',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 50.0,
            'status_code' => 200,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        ApiLog::create([
            'method' => 'POST',
            'endpoint' => '/api/tasks',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 300.0,
            'status_code' => 201,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        $response = $this->get('/api/logs?max_duration=100', ['Authorization' => 'Bearer ' . $this->token]);

        $response->assertResponseStatus(200);
        $data = json_decode($response->response->getContent(), true);
        $this->assertCount(1, $data['data']['data']);
        $this->assertEquals(50.0, $data['data']['data'][0]['duration_ms']);
    }

    /**
     * Test response includes pagination metadata
     */
    public function test_response_includes_pagination_metadata()
    {
        ApiLog::create([
            'method' => 'GET',
            'endpoint' => '/api/users',
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => 150.5,
            'status_code' => 200,
            'user_agent' => 'PostmanRuntime/7.32.3',
            'ip' => '127.0.0.1'
        ]);

        $response = $this->get('/api/logs', ['Authorization' => 'Bearer ' . $this->token]);
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
        $response = $this->get('/api/logs?search=users&method=GET&status_code_range=2xx&sort_by=timestamp&sort_order=desc', ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(200);

        $data = json_decode($response->response->getContent(), true);
        $this->assertArrayHasKey('filters', $data['data']);
        $this->assertEquals('users', $data['data']['filters']['search']);
        $this->assertEquals('GET', $data['data']['filters']['method']);
        $this->assertEquals('2xx', $data['data']['filters']['status_code_range']);
        $this->assertEquals('timestamp', $data['data']['filters']['sort_by']);
        $this->assertEquals('desc', $data['data']['filters']['sort_order']);
    }

    /**
     * Test validation of status code range filter parameter
     */
    public function test_validates_status_code_range_parameter()
    {
        $response = $this->get('/api/logs?status_code_range=invalid_range', ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(422);
        $response->seeJsonStructure(['errors' => ['status_code_range']]);
    }

    /**
     * Test validation of duration filter parameters
     */
    public function test_validates_duration_filter_parameters()
    {
        $response = $this->get('/api/logs?min_duration=-10', ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(422);
        $response->seeJsonStructure(['errors' => ['min_duration']]);

        $response = $this->get('/api/logs?max_duration=-10', ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(422);
        $response->seeJsonStructure(['errors' => ['max_duration']]);
    }

    /**
     * Test validation of duration range parameters
     */
    public function test_validates_duration_range_parameters()
    {
        $response = $this->get('/api/logs?min_duration=500&max_duration=100', ['Authorization' => 'Bearer ' . $this->token]);
        $response->assertResponseStatus(422);
        $response->seeJsonStructure(['errors' => ['max_duration']]);
    }
}
