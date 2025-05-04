<?php
// Simple API test file to check if API endpoints are working

header('Content-Type: application/json');

// Return a simple JSON response
echo json_encode([
    'success' => true,
    'message' => 'API test successful',
    'timestamp' => time()
]);