<?php
// FILE: /tests/test_api_authorization.php

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../config/config.php';

echo "Testing API Authorization...\n";

$db = Database::getInstance();

// Get a valid API key from database
$sql = "SELECT api_key, tenant_id FROM tenant_api_keys WHERE is_active = 1 LIMIT 1";
$apiKeyData = $db->fetch($sql);

if (!$apiKeyData) {
    echo "✗ No API keys found in database\n";
    exit(1);
}

echo "✓ API key retrieved from database\n";
echo "  Tenant ID: " . $apiKeyData['tenant_id'] . "\n";

// Test 1: Request without API key
echo "\nTest 1: Request without API key\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, BASE_URL . '/api/content');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 401) {
    echo "✓ Unauthorized access denied (401)\n";
} else {
    echo "✗ Expected 401, got " . $httpCode . "\n";
}

// Test 2: Request with valid API key
echo "\nTest 2: Request with valid API key\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, BASE_URL . '/api/content');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-KEY: ' . $apiKeyData['api_key']
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✓ Authorized access successful (200)\n";
    $data = json_decode($response, true);
    if (isset($data['status']) && $data['status'] === 'success') {
        echo "✓ Valid JSON response received\n";
        echo "  Content items: " . (isset($data['data']) ? count($data['data']) : 0) . "\n";
    }
} else {
    echo "✗ Expected 200, got " . $httpCode . "\n";
    echo "  Response: " . $response . "\n";
}

// Test 3: Request with invalid API key
echo "\nTest 3: Request with invalid API key\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, BASE_URL . '/api/content');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-KEY: invalid_key_12345'
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 401) {
    echo "✓ Invalid API key rejected (401)\n";
} else {
    echo "✗ Expected 401, got " . $httpCode . "\n";
}

echo "\nAPI Authorization tests completed!\n";
echo "\nNote: For full testing, ensure the web server is running at: " . BASE_URL . "\n";
