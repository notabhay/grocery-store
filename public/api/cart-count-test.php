<?php
// Simple cart count test API endpoint
// This file bypasses the normal routing system to test if direct API access works

// Set appropriate headers
header('Content-Type: application/json');

// Return a simple JSON response with a fixed cart count
echo json_encode([
    'count' => 5,
    'test' => true,
    'timestamp' => time()
]);