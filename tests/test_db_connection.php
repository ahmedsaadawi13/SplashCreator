<?php
// FILE: /tests/test_db_connection.php

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../config/config.php';

echo "Testing Database Connection...\n";

try {
    $db = Database::getInstance();
    echo "✓ Database connection successful\n";

    // Test query
    $result = $db->fetch("SELECT COUNT(*) as count FROM tenants");
    echo "✓ Query execution successful\n";
    echo "  Tenants in database: " . $result['count'] . "\n";

    // Test transactions
    $db->beginTransaction();
    $db->rollBack();
    echo "✓ Transaction support working\n";

    echo "\nAll database tests passed!\n";
} catch (Exception $e) {
    echo "✗ Database test failed: " . $e->getMessage() . "\n";
    exit(1);
}
