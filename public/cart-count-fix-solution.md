# Cart Count API Fix Solution

## Problem Analysis

The issue is that the cart count API endpoint is not working correctly. When the JavaScript tries to fetch from `https://teach.scam.keele.ac.uk/prin/y1d13/advanced-web-technologies/grocery-store/api/cart/count`, it's receiving the HTML of the homepage instead of a JSON response.

After investigation, we've identified several potential issues:

1. The server's URL routing is not correctly handling API requests
2. The .htaccess files might be redirecting API requests to the homepage
3. The API endpoint might require authentication that isn't being provided

## Solution Options

We've created several test files to diagnose and fix the issue:

1. **Direct API Endpoint**: `direct-cart-count.php` - A standalone PHP file that provides the cart count without going through the routing system
2. **API Test Files**: Various test files to check different URL formats and diagnose the issue
3. **Server Information**: `server-info.php` - Displays detailed server configuration information

## Recommended Fix

The simplest solution is to modify the `script.js` file to use the direct cart count endpoint instead of the API route. Here's how to implement this fix:

1. Create the direct cart count endpoint file (`direct-cart-count.php`) in the public directory
2. Modify the `fetchCartCount` function in `script.js` to use this direct endpoint

### Step 1: Create Direct Cart Count Endpoint

Create a file named `direct-cart-count.php` in the public directory with the following content:

```php
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
```

### Step 2: Modify the fetchCartCount Function in script.js

Modify the `fetchCartCount` function in `script.js` (around line 1053) to use the direct endpoint:

```javascript
async function fetchCartCount() {
  // If badge element doesn't exist, no need to fetch
  if (!cartCountBadge) {
    return 0;
  }
  try {
    // Log the exact URL being requested - using the direct endpoint
    const directEndpointUrl = `${window.baseUrl}direct-cart-count.php`;
    console.log(
      "Attempting to fetch cart count from direct URL:",
      directEndpointUrl
    );

    // Fetch count from direct API endpoint
    const response = await fetch(directEndpointUrl, {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
    });
    // Handle HTTP errors
    if (!response.ok) {
      const errorText = await response
        .text()
        .catch(() => "Could not read error response body");
      throw new Error(`HTTP error! status: ${response.status}. ${errorText}`);
    }
    // Check content type and parse JSON
    const contentType = response.headers.get("content-type");
    if (contentType && contentType.indexOf("application/json") !== -1) {
      const data = await response.json();
      // Validate the count received
      if (data && typeof data.count === "number" && data.count >= 0) {
        return data.count;
      } else {
        console.error(
          "Invalid cart count data format or negative count received:",
          data
        );
        return 0; // Return 0 for invalid data
      }
    } else {
      // Handle non-JSON responses
      const text = await response.text();
      throw new Error(
        "Expected JSON response for cart count, got non-JSON: " + text
      );
    }
  } catch (error) {
    // Handle fetch errors - log the full error object for more details
    console.error("Error fetching cart count:", error); // Log the full error object
    // Keep the existing error message log as well if desired, or combine them.
    console.error(
      `Error message: ${error.message}. Status: ${
        error.response ? error.response.status : "N/A"
      }`
    );
    return 0; // Return 0 on error
  }
}
```

## Long-Term Solution

For a more permanent solution, you should investigate why the API routing isn't working correctly. This might involve:

1. Checking the server's Apache configuration
2. Reviewing the .htaccess files to ensure they're correctly handling API requests
3. Modifying the Router class to better handle API routes
4. Adding proper CORS headers to API responses

The diagnostic files we've created should help with this investigation.
