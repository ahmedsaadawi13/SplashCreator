<?php
// FILE: /tests/test_ai_image_generation.php

require_once __DIR__ . '/../app/helpers/AIImageHelper.php';

echo "Testing AI Image Generation...\n";

$prompt = "Modern workspace with laptop and coffee";

echo "Generating image: " . $prompt . "\n";

$result = AIImageHelper::generate($prompt, [
    'style' => 'realistic',
    'width' => 1024,
    'height' => 1024,
    'tenant_id' => 1
]);

if ($result['success']) {
    echo "✓ Image generation successful\n";
    echo "  Image path: " . $result['image_path'] . "\n";
    echo "  Dimensions: " . $result['width'] . "x" . $result['height'] . "\n";
    echo "  Style: " . $result['style'] . "\n";

    // Check if file exists
    $fullPath = __DIR__ . '/../' . $result['image_path'];
    if (file_exists($fullPath)) {
        echo "✓ Image file created successfully\n";
        echo "  File size: " . filesize($fullPath) . " bytes\n";
    } else {
        echo "✗ Image file not found\n";
    }
} else {
    echo "✗ Image generation failed\n";
}

echo "\nAI Image Generation tests completed!\n";
