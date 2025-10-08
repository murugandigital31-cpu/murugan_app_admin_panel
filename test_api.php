<?php

// Simple test script to check API endpoints
$baseUrl = 'http://127.0.0.1:8001/api/v1/todays-arrivals';

$endpoints = [
    'direct-test' => $baseUrl . '/direct-test',
    'simple-test' => $baseUrl . '/simple-test', 
    'main-endpoint' => $baseUrl,
    'minimal' => $baseUrl . '/minimal',
    'debug' => $baseUrl . '/debug'
];

foreach ($endpoints as $name => $url) {
    echo "Testing $name: $url\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            'timeout' => 10
        ]
    ]);
    
    $result = @file_get_contents($url, false, $context);
    
    if ($result === false) {
        echo "❌ ERROR: Could not connect or get response\n";
    } else {
        // Check if it's valid JSON
        $json = json_decode($result, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "✅ SUCCESS: Valid JSON response\n";
            echo "Response: " . substr($result, 0, 200) . (strlen($result) > 200 ? '...' : '') . "\n";
        } else {
            echo "❌ ERROR: Invalid JSON response\n";
            echo "Raw response: " . substr($result, 0, 200) . (strlen($result) > 200 ? '...' : '') . "\n";
        }
    }
    echo "---\n";
}

echo "Test completed.\n";