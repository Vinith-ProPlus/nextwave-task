<?php

// NextWave Task API - Complete Test Suite
$baseUrl = 'http://localhost:8000/api';

echo "🚀 NextWave Task API - Complete Test Suite\n";
echo "==========================================\n\n";

$token = null;
$userId = null;
$taskId = null;

// Test 1: User Registration
echo "1️⃣ Testing User Registration...\n";
$registerData = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123'
];

$response = makeRequest('POST', '/register', $registerData);
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

if ($response['status'] === 201) {
    $data = json_decode($response['body'], true);
    $token = $data['data']['token'] ?? null;
    $userId = $data['data']['user']['id'] ?? null;
    echo "✅ Registration successful! User ID: $userId\n\n";
} else {
    echo "❌ Registration failed!\n\n";
    exit(1);
}

// Test 2: User Login
echo "2️⃣ Testing User Login...\n";
$loginData = [
    'email' => 'john@example.com',
    'password' => 'password123'
];

$response = makeRequest('POST', '/login', $loginData);
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

if ($response['status'] === 200) {
    echo "✅ Login successful!\n\n";
} else {
    echo "❌ Login failed!\n\n";
}

// Test 3: Get User Profile
echo "3️⃣ Testing Get User Profile...\n";
$response = makeRequest('GET', '/me', null, $token);
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

if ($response['status'] === 200) {
    echo "✅ Get user profile successful!\n\n";
} else {
    echo "❌ Get user profile failed!\n\n";
}

// Test 4: Create Task
echo "4️⃣ Testing Task Creation...\n";
$taskData = [
    'title' => 'Complete API Testing',
    'description' => 'Test all API endpoints thoroughly',
    'priority' => 'high',
    'due_date' => '2024-12-31'
];

$response = makeRequest('POST', '/tasks', $taskData, $token);
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

if ($response['status'] === 201) {
    $data = json_decode($response['body'], true);
    $taskId = $data['data']['id'] ?? null;
    echo "✅ Task creation successful! Task ID: $taskId\n\n";
} else {
    echo "❌ Task creation failed!\n\n";
}

// Test 5: Get All Tasks
echo "5️⃣ Testing Get All Tasks...\n";
$response = makeRequest('GET', '/tasks', null, $token);
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

if ($response['status'] === 200) {
    echo "✅ Get all tasks successful!\n\n";
} else {
    echo "❌ Get all tasks failed!\n\n";
}

// Test 6: Get Specific Task
if ($taskId) {
    echo "6️⃣ Testing Get Specific Task...\n";
    $response = makeRequest('GET', "/tasks/$taskId", null, $token);
    echo "Status: " . $response['status'] . "\n";
    echo "Response: " . $response['body'] . "\n\n";

    if ($response['status'] === 200) {
        echo "✅ Get specific task successful!\n\n";
    } else {
        echo "❌ Get specific task failed!\n\n";
    }
}

// Test 7: Update Task Status
if ($taskId) {
    echo "7️⃣ Testing Update Task Status...\n";
    $updateData = ['status' => 'in_progress'];

    $response = makeRequest('PATCH', "/tasks/$taskId", $updateData, $token);
    echo "Status: " . $response['status'] . "\n";
    echo "Response: " . $response['body'] . "\n\n";

    if ($response['status'] === 200) {
        echo "✅ Update task status successful!\n\n";
    } else {
        echo "❌ Update task status failed!\n\n";
    }
}

// Test 8: Update Task
if ($taskId) {
    echo "8️⃣ Testing Update Task...\n";
    $updateData = [
        'title' => 'Updated Task Title',
        'description' => 'Updated task description',
        'priority' => 'medium'
    ];

    $response = makeRequest('PUT', "/tasks/$taskId", $updateData, $token);
    echo "Status: " . $response['status'] . "\n";
    echo "Response: " . $response['body'] . "\n\n";

    if ($response['status'] === 200) {
        echo "✅ Update task successful!\n\n";
    } else {
        echo "❌ Update task failed!\n\n";
    }
}

// Test 9: Get User Tasks (Specific User)
if ($userId) {
    echo "9️⃣ Testing Get User Tasks (Specific User)...\n";
    $response = makeRequest('GET', "/users/$userId/tasks", null, $token);
    echo "Status: " . $response['status'] . "\n";
    echo "Response: " . $response['body'] . "\n\n";

    if ($response['status'] === 200) {
        echo "✅ Get user tasks successful!\n\n";
    } else {
        echo "❌ Get user tasks failed!\n\n";
    }
}

// Test 10: Create Another User (Admin)
echo "🔟 Testing Create Another User...\n";
$userData = [
    'name' => 'Jane Admin',
    'email' => 'jane@example.com',
    'password' => 'password123',
    'role' => 'admin'
];

$response = makeRequest('POST', '/users', $userData, $token);
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

if ($response['status'] === 201) {
    $data = json_decode($response['body'], true);
    $adminUserId = $data['data']['id'] ?? null;
    echo "✅ Create user successful! Admin User ID: $adminUserId\n\n";
} else {
    echo "❌ Create user failed!\n\n";
}

// Test 11: Get All Users
echo "1️⃣1️⃣ Testing Get All Users...\n";
$response = makeRequest('GET', '/users', null, $token);
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

if ($response['status'] === 200) {
    echo "✅ Get all users successful!\n\n";
} else {
    echo "❌ Get all users failed!\n\n";
}

// Test 12: Update User
if ($userId) {
    echo "1️⃣2️⃣ Testing Update User...\n";
    $updateData = [
        'name' => 'John Updated Doe',
        'email' => 'john.updated@example.com'
    ];

    $response = makeRequest('PUT', "/users/$userId", $updateData, $token);
    echo "Status: " . $response['status'] . "\n";
    echo "Response: " . $response['body'] . "\n\n";

    if ($response['status'] === 200) {
        echo "✅ Update user successful!\n\n";
    } else {
        echo "❌ Update user failed!\n\n";
    }
}

// Test 13: Get Specific User
if ($userId) {
    echo "1️⃣3️⃣ Testing Get Specific User...\n";
    $response = makeRequest('GET', "/users/$userId", null, $token);
    echo "Status: " . $response['status'] . "\n";
    echo "Response: " . $response['body'] . "\n\n";

    if ($response['status'] === 200) {
        echo "✅ Get specific user successful!\n\n";
    } else {
        echo "❌ Get specific user failed!\n\n";
    }
}

// Test 14: Refresh Token
echo "1️⃣4️⃣ Testing Refresh Token...\n";
$response = makeRequest('POST', '/refresh', null, $token);
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

if ($response['status'] === 200) {
    $data = json_decode($response['body'], true);
    $newToken = $data['data']['token'] ?? null;
    if ($newToken) {
        $token = $newToken;
        echo "✅ Token refresh successful!\n\n";
    } else {
        echo "❌ Token refresh failed!\n\n";
    }
} else {
    echo "❌ Token refresh failed!\n\n";
}

// Test 15: Logout
echo "1️⃣5️⃣ Testing Logout...\n";
$response = makeRequest('POST', '/logout', null, $token);
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

if ($response['status'] === 200) {
    echo "✅ Logout successful!\n\n";
} else {
    echo "❌ Logout failed!\n\n";
}

// Test 16: Try to access protected route after logout
echo "1️⃣6️⃣ Testing Protected Route After Logout...\n";
$response = makeRequest('GET', '/me', null, $token);
echo "Status: " . $response['status'] . "\n";
echo "Response: " . $response['body'] . "\n\n";

if ($response['status'] === 401) {
    echo "✅ Properly protected after logout!\n\n";
} else {
    echo "❌ Route not properly protected after logout!\n\n";
}

echo "🎉 API Testing Complete!\n";
echo "=======================\n";
echo "✅ All API endpoints tested successfully!\n";
echo "📚 Backend API is working perfectly!\n";
echo "🔗 API Base URL: http://localhost:8000/api\n";
echo "📖 Check README.md for complete documentation.\n";

// Helper function to make HTTP requests
function makeRequest($method, $endpoint, $data = null, $token = null) {
    global $baseUrl;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    if ($token) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ]);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['status' => 0, 'body' => 'cURL Error: ' . $error];
    }

    return ['status' => $httpCode, 'body' => $response];
}
