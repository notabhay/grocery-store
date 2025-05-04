<?php

// --- BEGIN DIAGNOSTIC LOGGING ---
try {
    $logFilePath = dirname(__DIR__) . '/logs/app.log'; // Assumes BASE_PATH isn't defined yet
    $logMessage = sprintf(
        "[%s] Request Received: URI=[%s], SCRIPT_NAME=[%s], PHP_SELF=[%s], Calculated BASE_URL attempt starts...\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REQUEST_URI'] ?? 'N/A',
        $_SERVER['SCRIPT_NAME'] ?? 'N/A',
        $_SERVER['PHP_SELF'] ?? 'N/A'
    );
    // Use error_log for simplicity and robustness early in execution
    error_log($logMessage, 3, $logFilePath);
} catch (\Throwable $e) {
    // Log failure to log, if possible
    error_log("Failed to write diagnostic log: " . $e->getMessage() . "\n", 3, $logFilePath);
}
// --- END DIAGNOSTIC LOGGING ---

/**
 * Front Controller / Application Entry Point
 *
 * This script serves as the single entry point for all HTTP requests
 * to the application. It initializes the core components, sets up
 * the environment, handles the request, and dispatches it to the
 * appropriate controller action via the router.
 */

// Define the base path of the application directory.
// dirname(__DIR__) gets the parent directory of the current file's directory (public -> project root).
define('BASE_PATH', dirname(__DIR__));

// --- Define Base URL ---
// Determine Scheme (http/https) - More robust check
$scheme = 'http'; // Default to http
if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)) {
    // Standard HTTPS check
    $scheme = 'https';
} elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    // Check for proxy header (common)
    $scheme = 'https';
} elseif (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
    // Check for another proxy header (less common)
    $scheme = 'https';
} elseif (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
    // Check if running on standard HTTPS port (fallback)
    $scheme = 'https';
}

// Determine Host (including port if non-standard)
$host = $_SERVER['HTTP_HOST']; // e.g., localhost:8888 or teach.scam.keele.ac.uk

// Determine Base Path relative to document root
$script_dir = dirname($_SERVER['SCRIPT_NAME']);
// Normalize directory separator and ensure trailing slash
$base_path_url = rtrim(str_replace('\\', '/', $script_dir), '/') . '/';
// Handle root directory case where script_dir might be '/' or '\'
if ($base_path_url === '//') {
    $base_path_url = '/';
}

// Combine to form the Base URL
define('BASE_URL', $scheme . '://' . $host . $base_path_url);
// --- End Base URL Definition ---

// Include the Composer autoloader.
// This makes all Composer-managed libraries and application classes (following PSR-4) available.
require_once BASE_PATH . '/vendor/autoload.php';


// --- Check for required PHP extensions ---
$required_extensions = ['pdo_mysql', 'session', 'gd', 'mbstring', 'json']; // Add other potential extensions if needed
$missing_extensions = [];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}
if (!empty($missing_extensions)) {
    http_response_code(500); // Keep the 500 status but provide a message
    die('ERROR: Required PHP extension(s) missing: ' . implode(', ', $missing_extensions) . '. Please contact server administrator.');
}
// --- End extension check ---

// Load the application configuration.
// Contains settings like database credentials, debug mode, etc.
$config = require_once BASE_PATH . '/app/config.php';

// --- Dependency Imports ---
// Import necessary classes from the application's core, helpers, and external libraries.
use App\Core\Request;       // Handles incoming HTTP requests.
use App\Core\Router;        // Manages routing and dispatches requests.
use App\Core\Database;      // Handles database connections and queries.
use App\Core\Registry;      // A simple service container for dependency injection.
use App\Core\Session;       // Manages user sessions.
use App\Helpers\CaptchaHelper; // Helper for generating and validating CAPTCHAs.
use Monolog\Logger;         // PSR-3 compliant logging library.
use Monolog\Handler\StreamHandler; // Monolog handler to write logs to files.
use App\Helpers\SecurityHelper; // Helper for setting security-related HTTP headers.

// --- Initial Setup & Security ---

// Apply essential security headers to HTTP responses.
// Helps mitigate common web vulnerabilities like XSS, clickjacking, etc.
SecurityHelper::setSecurityHeaders();

// Bind the loaded configuration array to the Registry.
// Makes configuration accessible throughout the application via Registry::get('config').
Registry::bind('config', $config);
error_log("[DEBUG] index.php - Config bound to Registry."); // ADDED DEBUG LOG

// --- Core Services Initialization (Dependency Injection Setup) ---
error_log("[DEBUG] index.php - Entering Core Services Initialization block."); // ADDED DEBUG LOG
// This block sets up essential services and binds them to the Registry.
// It's wrapped in a try-catch to handle critical initialization errors.
try {
    error_log("[DEBUG] index.php - Inside try block for Core Services."); // ADDED DEBUG LOG
    // --- Logger Setup ---
    error_log("[DEBUG] index.php - Starting Logger Setup."); // ADDED DEBUG LOG
    // Define the path for the application log file.
    $logFilePath = BASE_PATH . '/logs/app.log';
    error_log("[DEBUG] index.php - Log file path: " . $logFilePath); // ADDED DEBUG LOG
    // Get the directory path for the log file.
    $logDir = dirname($logFilePath);
    error_log("[DEBUG] index.php - Log directory path: " . $logDir); // ADDED DEBUG LOG
    // Create the log directory if it doesn't exist.
    if (!is_dir($logDir)) {
        error_log("[DEBUG] index.php - Log directory does not exist. Attempting mkdir."); // ADDED DEBUG LOG
        // Recursively create the directory with appropriate permissions (0775).
        if (!mkdir($logDir, 0775, true) && !is_dir($logDir)) { // Check again after attempt
             error_log("[FATAL] index.php - Failed to create log directory: " . $logDir);
             throw new \RuntimeException(sprintf('Directory "%s" was not created', $logDir));
        }
         error_log("[DEBUG] index.php - mkdir finished or directory already exists."); // ADDED DEBUG LOG
    } else {
         error_log("[DEBUG] index.php - Log directory already exists."); // ADDED DEBUG LOG
    }
    // Instantiate the Logger. 'app' is the channel name.
    $logger = new Logger('app');
    error_log("[DEBUG] index.php - Logger instantiated."); // ADDED DEBUG LOG
    // Determine the minimum logging level based on the debug mode setting in config.
    // Log everything (Debug level) if DEBUG_MODE is true, otherwise log Warnings and above.
    $logLevel = ($config['DEBUG_MODE'] ?? false) ? Logger::DEBUG : Logger::WARNING;
    error_log("[DEBUG] index.php - Log level determined: " . $logLevel); // ADDED DEBUG LOG
    // Add a handler to write log records to the specified file with the determined level.
    $streamHandler = new StreamHandler($logFilePath, $logLevel);
    error_log("[DEBUG] index.php - StreamHandler instantiated."); // ADDED DEBUG LOG
    $logger->pushHandler($streamHandler);
    error_log("[DEBUG] index.php - StreamHandler pushed to Logger."); // ADDED DEBUG LOG
    // Bind the logger instance to the Registry.
    Registry::bind('logger', $logger);
    error_log("[DEBUG] index.php - Logger bound to Registry. Logger setup complete."); // ADDED DEBUG LOG

    // --- Database Setup ---
    error_log("[DEBUG] index.php - Starting Database Setup."); // ADDED DEBUG LOG
    // Instantiate the Database connection handler using credentials from the config.
    $db = new Database(
        $config['DB_HOST'],
        $config['DB_NAME'],
        $config['DB_USER'],
        $config['DB_PASS']
    );
    error_log("[DEBUG] index.php - Database wrapper instantiated."); // ADDED DEBUG LOG
    // Check if the database connection was successful.
    $pdoConnection = $db->getConnection(); // Attempt connection
    error_log("[DEBUG] index.php - Attempted DB connection. Result: " . ($pdoConnection ? 'OK' : 'FAILED')); // ADDED DEBUG LOG
    if ($pdoConnection) {
        // Bind the database instance to the Registry if connection is successful.
        Registry::bind('database', $db);
        error_log("[DEBUG] index.php - Database bound to Registry."); // ADDED DEBUG LOG
    } else {
        error_log("[FATAL] index.php - Database connection failed."); // ADDED ERROR LOG
        // Handle database connection failure gracefully.
        $errorMessage = "Database Connection Error: Sorry, we couldn't connect to the database. Please try again later.";
        // Log the critical error using the logger if available.
        if (Registry::has('logger')) {
            Registry::get('logger')->critical("Database connection failed.", ['error' => $db->getError()]);
        } else {
            // Fallback to PHP's built-in error log if logger isn't ready.
            error_log("CRITICAL: Database connection failed. Error: " . $db->getError());
        }
        // Set a 503 Service Unavailable HTTP status code.
        http_response_code(503);
        // Display a user-friendly error message.
        echo "<h1>Database Connection Error</h1>";
        echo "<p>Sorry, we couldn't connect to the database. Please try again later.</p>";
        // Display detailed error information if debug mode is enabled.
        if ($config['DEBUG_MODE'] ?? false) {
            echo "<p>Error details: " . htmlspecialchars($db->getError()) . "</p>";
        }
        // Terminate script execution as the application cannot proceed without a database.
        exit;
    }

    error_log("[DEBUG] index.php - Database setup complete."); // ADDED DEBUG LOG
    // --- Session Setup ---
    error_log("[DEBUG] index.php - Starting Session Setup."); // ADDED DEBUG LOG
    // Prepare session configuration options.
    $sessionConfig = [
        // Set session inactivity timeout from config (default: 1800 seconds / 30 minutes).
        'session_timeout' => $config['AUTH_TIMEOUT'] ?? 1800,
    ];
    // Instantiate the Session manager with the configuration.
    $session = new Session($sessionConfig);
    error_log("[DEBUG] index.php - Session instantiated."); // ADDED DEBUG LOG
    // Bind the session instance to the Registry.
    Registry::bind('session', $session);
    error_log("[DEBUG] index.php - Session bound to Registry. Session setup complete."); // ADDED DEBUG LOG

    // --- CAPTCHA Helper Setup ---
    error_log("[DEBUG] index.php - Starting CaptchaHelper Setup."); // ADDED DEBUG LOG
    // Instantiate the CaptchaHelper, passing the session instance for storing CAPTCHA codes.
    $captchaHelper = new CaptchaHelper($session);
    error_log("[DEBUG] index.php - CaptchaHelper instantiated."); // ADDED DEBUG LOG
    // Bind the CaptchaHelper instance to the Registry.
    Registry::bind('captchaHelper', $captchaHelper);
    error_log("[DEBUG] index.php - CaptchaHelper bound to Registry. Captcha setup complete."); // ADDED DEBUG LOG

    // --- Request Object Setup ---
    error_log("[DEBUG] index.php - Starting Request Setup."); // ADDED DEBUG LOG
    // Instantiate the Request object, which encapsulates the current HTTP request data (URI, method, POST/GET data).
    $request = new Request();
    error_log("[DEBUG] index.php - Request instantiated."); // ADDED DEBUG LOG
    // Bind the Request instance to the Registry.
    Registry::bind('request', $request);
    error_log("[DEBUG] index.php - Request bound to Registry. Request setup complete."); // ADDED DEBUG LOG
    error_log("[DEBUG] index.php - Exiting try block for Core Services successfully."); // ADDED DEBUG LOG
} catch (\Throwable $e) { // Catch Throwable
    error_log("[FATAL] index.php - Exception during Core Services Initialization: " . $e->getMessage() . "\n" . $e->getTraceAsString()); // ADDED ERROR LOG
    // --- Bootstrap Error Handling ---
    // Catch any exceptions that occur during the core service initialization phase.
    $errorMessage = "Bootstrap Error (DI Setup): " . $e->getMessage();
    // Log the critical error using the logger if available.
    if (Registry::has('logger')) {
        Registry::get('logger')->critical($errorMessage, ['exception' => $e]);
    } else {
        // Fallback to PHP's built-in error log.
        error_log("CRITICAL: " . $errorMessage);
    }
    // Set a 500 Internal Server Error HTTP status code.
    http_response_code(500);
    // Display a generic error message to the user.
    echo "<h1>Application Initialization Error</h1>";
    echo "<p>An error occurred during application setup. Please try again later.</p>";
    // Display detailed error information if debug mode is enabled.
    if ($config['DEBUG_MODE'] ?? false) {
        echo "<p>Error details: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    // Terminate script execution as a critical component failed to initialize.
    exit;
}

error_log("[DEBUG] index.php - Exited Core Services Initialization block."); // ADDED DEBUG LOG
// --- Session Start & Validation ---
error_log("[DEBUG] index.php - Starting Session Start & Validation."); // ADDED DEBUG LOG

// Explicitly start or resume the session.
// This must happen after core services (like the logger) are potentially available.
error_log("[DEBUG] index.php - Calling session->start()."); // ADDED DEBUG LOG
$sessionStartResult = $session->start();
error_log("[DEBUG] index.php - session->start() returned: " . ($sessionStartResult ? 'true' : 'false')); // ADDED DEBUG LOG
if (!$sessionStartResult) {
    // Log a critical error if the session fails to start.
    // Registry::get('logger')->critical("Failed to start session."); // Logger might not be reliable if session failed
    error_log("[FATAL] index.php - Failed to start session."); // ADDED ERROR LOG
    // Set a 500 Internal Server Error HTTP status code.
    http_response_code(500);
    // Display an error message.
    echo "<h1>Session Error</h1><p>Could not start session. Please check server configuration.</p>";
    // Terminate script execution.
    exit;
}

error_log("[DEBUG] index.php - Session started successfully."); // ADDED DEBUG LOG
// Validate the user's session activity (e.g., check for timeout).
// This might regenerate the session ID or log the user out if inactive for too long.
error_log("[DEBUG] index.php - Calling session->validateActivity()."); // ADDED DEBUG LOG
$session->validateActivity();
error_log("[DEBUG] index.php - session->validateActivity() completed."); // ADDED DEBUG LOG
error_log("[DEBUG] index.php - Session Start & Validation complete."); // ADDED DEBUG LOG

// --- Routing and Request Dispatching ---
// This block handles the incoming request by routing it to the correct controller action.
error_log("[DEBUG] index.php - Entering Routing and Request Dispatching block."); // ADDED DEBUG LOG
try {
    error_log("[DEBUG] index.php - Loading routes from " . BASE_PATH . '/app/routes.php'); // ADDED DEBUG LOG
    // Load the defined application routes from the routes file.
    $router = Router::load(BASE_PATH . '/app/routes.php');
    error_log("[DEBUG] index.php - Routes loaded. Calling router->direct()."); // ADDED DEBUG LOG
    // Then, direct the router to find a match for the current request URI and method.
    // The router will instantiate the controller and call the appropriate method.
    $router->direct($request->uri(), $request->method());
    error_log("[DEBUG] index.php - router->direct() completed successfully."); // ADDED DEBUG LOG
} catch (\Throwable $e) { // Catch Throwable
    error_log("[FATAL] index.php - Exception during Routing/Dispatching: " . $e->getMessage() . "\n" . $e->getTraceAsString()); // ADDED ERROR LOG
    // --- General Exception Handling ---
    // Catch any unhandled exceptions that occur during routing or controller execution.
    $logger = Registry::get('logger'); // Get the logger instance.
    // Log the error with exception details.
    $logger->error("Unhandled Exception: " . $e->getMessage(), ['exception' => $e]);

    // Determine the appropriate HTTP status code.
    // Use 404 for routing exceptions (often thrown by the router itself), otherwise default to 500.
    $statusCode = $e->getCode();
    // Default to 500 if code is not set or invalid
    if (!is_int($statusCode) || $statusCode < 100 || $statusCode > 599) {
        $statusCode = 500;
    }
    http_response_code($statusCode); // Set status code early

    // Check if it's a 404 for an API route
    if ($statusCode == 404) {
        // Get the requested path (relative to the application base)
        // Need to recalculate it here as it's not passed with the exception
        $requestUriPathForCheck = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        $baseUrlPathForCheck = parse_url(BASE_URL, PHP_URL_PATH);
        $requestPathCleanForCheck = trim($requestUriPathForCheck, '/');
        $basePathCleanForCheck = trim($baseUrlPathForCheck, '/');
        $appPathForCheck = '/';
        if (strpos($requestPathCleanForCheck, $basePathCleanForCheck) === 0) {
            $appPathSegmentForCheck = substr($requestPathCleanForCheck, strlen($basePathCleanForCheck));
            $appPathForCheck = '/' . ltrim($appPathSegmentForCheck, '/');
        } elseif (empty($basePathCleanForCheck) && !empty($requestPathCleanForCheck)) {
            $appPathForCheck = '/' . $requestPathCleanForCheck;
        }
        if (empty(trim($appPathForCheck, '/'))) {
            $appPathForCheck = '/';
        }

        // Check if the application-relative path starts with /api/
        if (strpos($appPathForCheck, '/api/') === 0) {
            // It's an API route 404, send JSON response
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not Found', 'message' => $e->getMessage()]);
            exit; // Stop execution after sending JSON response
        }
        // If not an API route, fall through to the existing HTML 404 handling below
    }

    // Display error details based on debug mode.
    if ($config['DEBUG_MODE'] ?? false) {
        // Show detailed error information including stack trace in debug mode.
        echo '<h1>Error (' . $statusCode . ')</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        // Show a generic error page in production mode.
        $displayMessage = '';
        // Check if there's a specific error flash message set in the session.
        if (Registry::has('session')) {
            $session = Registry::get('session');
            if ($session->hasFlash('error')) {
                // Retrieve and clear the flash message.
                $displayMessage = $session->getFlash('error');
            }
        }

        // Display appropriate message based on the status code.
        if ($statusCode == 404) {
            // This part only handles non-API 404s (API 404s are handled above)
            echo '<h1>404 - Page Not Found</h1>';
            echo '<p>Sorry, the page you are looking for could not be found.</p>';
        } else {
            echo '<h1>An Error Occurred</h1>';
            echo '<p>We are sorry, something went wrong. Please try again later.</p>';
            // If a specific flash message exists, display it.
            if (!empty($displayMessage)) {
                echo '<p>Details: ' . htmlspecialchars($displayMessage) . '</p>';
            }
        }
    }
    // Note: Script execution implicitly ends here after handling the exception.
}