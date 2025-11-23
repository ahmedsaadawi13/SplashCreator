<?php
// FILE: /tests/test_ai_text_generation.php

require_once __DIR__ . '/../app/helpers/AITextHelper.php';

echo "Testing AI Text Generation...\n";

$testPrompts = [
    'Write an Instagram caption about morning coffee',
    'Create a tweet about productivity',
    'Write a LinkedIn post about leadership'
];

foreach ($testPrompts as $i => $prompt) {
    echo "\nTest " . ($i + 1) . ": " . $prompt . "\n";

    $result = AITextHelper::generate($prompt);

    if ($result['success']) {
        echo "✓ Generation successful\n";
        echo "  Model: " . $result['model'] . "\n";
        echo "  Tokens: " . $result['tokens'] . "\n";
        echo "  Preview: " . substr($result['text'], 0, 100) . "...\n";
    } else {
        echo "✗ Generation failed\n";
    }
}

echo "\nAI Text Generation tests completed!\n";
