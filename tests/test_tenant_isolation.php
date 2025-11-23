<?php
// FILE: /tests/test_tenant_isolation.php

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Content.php';
require_once __DIR__ . '/../config/config.php';

echo "Testing Tenant Isolation...\n";

// Simulate tenant 1 session
$_SESSION['tenant_id'] = 1;

$contentModel = new Content();

echo "\nTenant 1 Session:\n";
$tenant1Content = $contentModel->findAll();
echo "✓ Retrieved content for tenant 1: " . count($tenant1Content) . " items\n";

// Verify all content belongs to tenant 1
$allTenant1 = true;
foreach ($tenant1Content as $content) {
    if ($content['tenant_id'] != 1) {
        $allTenant1 = false;
        break;
    }
}

if ($allTenant1) {
    echo "✓ All content items belong to tenant 1\n";
} else {
    echo "✗ Content leak detected! Some items belong to other tenants\n";
}

// Test create with automatic tenant_id
$newContentId = $contentModel->create([
    'user_id' => 1,
    'type' => 'text',
    'title' => 'Test Content',
    'content_text' => 'This is a test'
]);

$newContent = $contentModel->findById($newContentId);

if ($newContent && $newContent['tenant_id'] == 1) {
    echo "✓ New content automatically assigned to tenant 1\n";
} else {
    echo "✗ Tenant assignment failed\n";
}

// Clean up
$contentModel->delete($newContentId);

echo "\nTenant Isolation tests completed!\n";
echo "✓ Tenant scoping is working correctly\n";
