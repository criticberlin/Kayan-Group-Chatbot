<?php

// Test script to check if ChatService finds products
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$chatService = new \App\Services\ChatService();

echo "Testing ChatService product search...\n\n";

// Test 1: Search for chocolate
$message = "What chocolate products do you have?";
echo "Message: {$message}\n";
$context = $chatService->generateProductContext($message, 5);
echo "Products found: " . count($context) . "\n";
echo json_encode($context, JSON_PRETTY_PRINT) . "\n";
