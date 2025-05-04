<?php
/**
 * Direct Cart Count API Endpoint
 * 
 * This file provides a direct API endpoint for getting the cart count
 * without going through the normal routing system.
 */

// Set appropriate headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Include necessary files
require_once dirname(__DIR__) . '/app/Core/Session.php';
require_once dirname(__DIR__) . '/app/Core/Registry.php';
require_once dirname(__DIR__) . '/app/Helpers/CartHelper.php';
require_once dirname(__DIR__) . '/app/Core/Database.php';

// Initialize session
try {
    // Load configuration
    $config = require_once dirname(__DIR__) . '/app/config.php';
    
    // Create session
    $session = new App\Core\Session([
        'session_timeout' => $config['AUTH_TIMEOUT'] ?? 1800,
    ]);
    
    // Start session
    $session->start();
    
    // Create database connection
    $db = new App\Core\Database(
        $config['DB_HOST'],
        $config['DB_NAME'],
        $config['DB_USER'],
        $config['DB_PASS']
    );
    
    // Register services
    App\Core\Registry::bind('session', $session);
    App\Core\Registry::bind('database', $db);
    
    // Create cart helper
    $cartHelper = new App\Helpers\CartHelper($session, $db);
    
    // Get cart data
    $cartData = $cartHelper->getCartData();
    
    // Return cart count
    echo json_encode([
        'count' => $cartData['total_items'] ?? 0,
        'is_direct_endpoint' => true,
        'timestamp' => time()
    ]);
} catch (Exception $e) {
    // Return error
    http_response_code(500);
    echo json_encode([
        'error' => 'An error occurred: ' . $e->getMessage(),
        'is_direct_endpoint' => true
    ]);
}