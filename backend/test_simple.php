<?php

echo "Testing NextWave Task API\n";
echo "========================\n\n";

$baseUrl = 'http://localhost:8000/api';

// Test 1: Register
echo "1. Testing Registration...\n";
$data = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/register');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: $httpCode\n";
echo "Response: $response\n\n";

if ($httpCode === 201) {
    $responseData = json_decode($response, true);
    $token = $responseData['data']['token'] ?? null;

    if ($token) {
        echo "✅ Registration successful! Token: " . substr($token, 0, 20) . "...\n\n";

        // Test 2: Login
        echo "2. Testing Login...\n";
        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl . '/login');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        echo "Status: $httpCode\n";
        echo "Response: $response\n\n";

        if ($httpCode === 200) {
            echo "✅ Login successful!\n\n";

            // Test 3: Create Task
            echo "3. Testing Task Creation...\n";
            $taskData = [
                'title' => 'Test Task',
                'description' => 'This is a test task',
                'priority' => 'high'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $baseUrl . '/tasks');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($taskData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            echo "Status: $httpCode\n";
            echo "Response: $response\n\n";

            if ($httpCode === 201) {
                echo "✅ Task creation successful!\n\n";

                // Test 4: Get Tasks
                echo "4. Testing Get Tasks...\n";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $baseUrl . '/tasks');
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $token
                ]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                echo "Status: $httpCode\n";
                echo "Response: $response\n\n";

                if ($httpCode === 200) {
                    echo "✅ Get tasks successful!\n\n";
                }
            }
        }
    }
} else {
    echo "❌ Registration failed!\n\n";
}

echo "API Testing Complete!\n";
