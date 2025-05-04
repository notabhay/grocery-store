<?php
// Server Information and Diagnostic Script
// This script displays information about the server configuration and request details

// Set content type to plain text for easier reading
header('Content-Type: text/plain');

// Function to safely display variables that might not be set
function safe_var($var, $default = 'Not set') {
    return isset($var) ? $var : $default;
}

// Basic server information
echo "=== SERVER INFORMATION ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . safe_var($_SERVER['SERVER_SOFTWARE']) . "\n";
echo "Document Root: " . safe_var($_SERVER['DOCUMENT_ROOT']) . "\n";
echo "Server Name: " . safe_var($_SERVER['SERVER_NAME']) . "\n";
echo "Server Protocol: " . safe_var($_SERVER['SERVER_PROTOCOL']) . "\n";
echo "Server Port: " . safe_var($_SERVER['SERVER_PORT']) . "\n";
echo "HTTPS: " . (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'off') . "\n";
echo "\n";

// Request information
echo "=== REQUEST INFORMATION ===\n";
echo "Request URI: " . safe_var($_SERVER['REQUEST_URI']) . "\n";
echo "Script Name: " . safe_var($_SERVER['SCRIPT_NAME']) . "\n";
echo "PHP Self: " . safe_var($_SERVER['PHP_SELF']) . "\n";
echo "Query String: " . safe_var($_SERVER['QUERY_STRING']) . "\n";
echo "Request Method: " . safe_var($_SERVER['REQUEST_METHOD']) . "\n";
echo "Path Info: " . safe_var($_SERVER['PATH_INFO']) . "\n";
echo "Path Translated: " . safe_var($_SERVER['PATH_TRANSLATED']) . "\n";
echo "\n";

// Calculated paths
echo "=== CALCULATED PATHS ===\n";
$requestUriPath = parse_url(safe_var($_SERVER['REQUEST_URI']), PHP_URL_PATH) ?: '/';
echo "Parsed Request URI Path: " . $requestUriPath . "\n";

// Try to determine the base URL
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = safe_var($_SERVER['HTTP_HOST']);
$scriptDir = dirname(safe_var($_SERVER['SCRIPT_NAME']));
$basePathUrl = rtrim(str_replace('\\', '/', $scriptDir), '/') . '/';
if ($basePathUrl === '//') {
    $basePathUrl = '/';
}
$calculatedBaseUrl = $scheme . '://' . $host . $basePathUrl;
echo "Calculated Base URL: " . $calculatedBaseUrl . "\n";

// Try to determine the application-relative path
$baseUrlPath = parse_url($calculatedBaseUrl, PHP_URL_PATH);
$requestPathClean = trim($requestUriPath, '/');
$basePathClean = trim($baseUrlPath, '/');
$appPath = '/';
if (!empty($basePathClean) && strpos($requestPathClean, $basePathClean) === 0) {
    $appPathSegment = substr($requestPathClean, strlen($basePathClean));
    $appPath = '/' . ltrim($appPathSegment, '/');
} elseif (empty($basePathClean) && !empty($requestPathClean)) {
    $appPath = '/' . $requestPathClean;
}
if (empty(trim($appPath, '/'))) {
    $appPath = '/';
}
echo "Application-Relative Path: " . $appPath . "\n";
echo "\n";

// Apache modules (if available)
echo "=== APACHE MODULES ===\n";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "mod_rewrite enabled: " . (in_array('mod_rewrite', $modules) ? 'Yes' : 'No') . "\n";
    echo "All modules: " . implode(', ', $modules) . "\n";
} else {
    echo "Apache modules information not available.\n";
}
echo "\n";

// .htaccess file check
echo "=== .HTACCESS FILES ===\n";
$rootHtaccess = dirname(__DIR__) . '/.htaccess';
$publicHtaccess = __DIR__ . '/.htaccess';
echo "Root .htaccess exists: " . (file_exists($rootHtaccess) ? 'Yes' : 'No') . "\n";
echo "Public .htaccess exists: " . (file_exists($publicHtaccess) ? 'Yes' : 'No') . "\n";
echo "\n";

// PHP configuration
echo "=== PHP CONFIGURATION ===\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "error_reporting: " . ini_get('error_reporting') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "allow_url_fopen: " . ini_get('allow_url_fopen') . "\n";
echo "\n";

// Headers sent to the client
echo "=== REQUEST HEADERS ===\n";
$headers = getallheaders();
foreach ($headers as $name => $value) {
    echo "$name: $value\n";
}
echo "\n";

// Environment variables
echo "=== ENVIRONMENT VARIABLES ===\n";
foreach ($_ENV as $name => $value) {
    echo "$name: $value\n";
}
echo "\n";

// Include paths
echo "=== INCLUDE PATHS ===\n";
echo get_include_path() . "\n";
echo "\n";

// Loaded extensions
echo "=== LOADED EXTENSIONS ===\n";
$extensions = get_loaded_extensions();
sort($extensions);
echo implode(', ', $extensions) . "\n";
echo "\n";

// End of script
echo "=== END OF DIAGNOSTIC INFORMATION ===\n";