<?php
// FILE: /tests/test_ai_video_generation.php

require_once __DIR__ . '/../app/helpers/AIVideoHelper.php';

echo "Testing AI Video Generation...\n";

$script = "Welcome to our platform! We create amazing content that helps you grow your business.";

echo "Generating video from script...\n";

$result = AIVideoHelper::generate($script, [
    'resolution' => '1920x1080',
    'tenant_id' => 1
]);

if ($result['success']) {
    echo "✓ Video generation successful\n";
    echo "  Video path: " . $result['video_path'] . "\n";
    echo "  Duration: " . $result['duration_seconds'] . " seconds\n";
    echo "  Resolution: " . $result['resolution'] . "\n";

    // Check if file exists
    $fullPath = __DIR__ . '/../' . $result['video_path'];
    if (file_exists($fullPath)) {
        echo "✓ Video file created successfully\n";
        echo "  File size: " . filesize($fullPath) . " bytes\n";
    } else {
        echo "✗ Video file not found\n";
    }
} else {
    echo "✗ Video generation failed\n";
}

echo "\nAI Video Generation tests completed!\n";
