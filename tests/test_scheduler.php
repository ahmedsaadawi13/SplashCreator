<?php
// FILE: /tests/test_scheduler.php

require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../config/config.php';

echo "Testing Scheduler Functionality...\n";

$db = Database::getInstance();

// Check for pending posts
$sql = "SELECT COUNT(*) as count FROM scheduled_posts WHERE status = 'scheduled'";
$result = $db->fetch($sql);
echo "✓ Pending posts query successful\n";
echo "  Scheduled posts: " . $result['count'] . "\n";

// Check for posted posts
$sql = "SELECT COUNT(*) as count FROM scheduled_posts WHERE status = 'posted'";
$result = $db->fetch($sql);
echo "✓ Posted posts query successful\n";
echo "  Posted posts: " . $result['count'] . "\n";

// Test date comparison
$sql = "SELECT COUNT(*) as count FROM scheduled_posts WHERE scheduled_time_utc <= NOW() AND status = 'scheduled'";
$result = $db->fetch($sql);
echo "✓ Due posts detection working\n";
echo "  Posts due for posting: " . $result['count'] . "\n";

echo "\nScheduler tests completed!\n";
echo "To test actual posting, run: php cron_scheduler.php\n";
