<?php

require_once 'vendor/autoload.php';

// Bootstrap the application
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test the AuthController directly
$controller = new \App\Http\Controllers\AuthController();

// Test data
$requestData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123'
];

// Create a mock request
$request = new \Illuminate\Http\Request();
$request->merge($requestData);

echo "Testing AuthController register method...\n";

try {
    $response = $controller->register($request);
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content: " . $response->getContent() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
