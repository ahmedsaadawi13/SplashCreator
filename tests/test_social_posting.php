<?php
// FILE: /tests/test_social_posting.php

require_once __DIR__ . '/../app/helpers/SocialPosterHelper.php';

echo "Testing Social Media Posting (Simulated)...\n";

$platforms = ['instagram', 'facebook', 'twitter', 'linkedin', 'tiktok', 'youtube'];

foreach ($platforms as $platform) {
    echo "\nTesting {$platform}...\n";

    $content = [
        'text' => 'This is a test post for ' . $platform,
        'media_path' => 'storage/uploads/1/generated/test.jpg'
    ];

    $result = SocialPosterHelper::post($platform, $content, 1);

    if ($result['status'] === 'success') {
        echo "✓ Post successful\n";
        echo "  Message: " . $result['message'] . "\n";
        echo "  Post URL: " . $result['url'] . "\n";
    } else {
        echo "✗ Post failed\n";
        echo "  Message: " . $result['message'] . "\n";
    }
}

// Check if log file was created
$logFile = __DIR__ . '/../storage/logs/social_post_log.txt';
if (file_exists($logFile)) {
    echo "\n✓ Social post log file created\n";
    echo "  Log file: " . $logFile . "\n";
    echo "  Last 3 entries:\n";
    $lines = file($logFile);
    $lastLines = array_slice($lines, -3);
    foreach ($lastLines as $line) {
        echo "  " . $line;
    }
} else {
    echo "\n✗ Social post log file not found\n";
}

echo "\nSocial Posting tests completed!\n";
