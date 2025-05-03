<?php

namespace App\Core;

use App\Core\Registry; // Import Registry for accessing shared services like the logger.

/**
 * Class Redirect
 *
 * Provides static utility methods for handling HTTP redirects.
 * Simplifies redirecting to specific URLs or back to the previous page.
 *
 * @package App\Core
 */
class Redirect
{
    /**
     * Redirects the user to a specified URL.
     *
     * Prepends BASE_URL to root-relative paths (starting with '/' but not '//').
     * Sends a 'Location' header to the browser to initiate the redirect.
     * Validates the final URL format. If invalid, logs a warning and displays an error.
     * Cleans any existing output buffers before sending headers.
     *
     * @param string $url The URL to redirect to. Can be root-relative, protocol-relative, or absolute.
     * @param int $statusCode The HTTP status code for the redirect (default: 302 Found).
     * @return void This method terminates script execution using exit() after sending the header.
     */
    public static function to(string $url, int $statusCode = 302): void
    {
        $finalUrl = $url;

        // Check if it's a root-relative path (starts with '/' but not '//')
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            // Prepend BASE_URL if it's defined
            if (defined('BASE_URL')) {
                // Ensure BASE_URL ends with '/' and url starts with '/' are handled correctly
                $finalUrl = rtrim(BASE_URL, '/') . '/' . ltrim($url, '/');
            } else {
                // Log error if BASE_URL is not defined but needed
                if (Registry::has('logger')) {
                    Registry::get('logger')->error("BASE_URL constant is not defined, cannot redirect relative path.", ['url' => $url]);
                }
                http_response_code(500);
                echo "Error: Cannot redirect relative path because BASE_URL is not defined.";
                exit();
            }
        }
        // Validate the final URL format (must be protocol-relative or absolute)
        elseif (strpos($finalUrl, '//') !== 0 && strpos($finalUrl, 'http:') !== 0 && strpos($finalUrl, 'https:') !== 0) {
            // Log a warning if the URL format is invalid.
            if (Registry::has('logger')) {
                Registry::get('logger')->warning("Invalid redirect URL format provided.", ['url' => $url, 'finalUrl' => $finalUrl]);
            }
            // Display an error message and terminate execution.
            http_response_code(500);
            echo "Error: Invalid redirect URL specified.";
            exit();
        }

        // Clean any active output buffers to prevent header errors.
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Send the Location header with the final URL and status code.
        header('Location: ' . $finalUrl, true, $statusCode);
        // Terminate script execution immediately after sending the header.
        exit();
    }

    /**
     * Redirects the user back to the referring page or a fallback URL.
     *
     * Uses the 'HTTP_REFERER' server variable. If not set, it uses the provided
     * fallback URL, defaulting to BASE_URL if available, otherwise '/'.
     *
     * @param string|null $fallbackUrl The URL to redirect to if HTTP_REFERER is not available.
     *                                 Defaults to BASE_URL if defined, otherwise '/'.
     * @param int $statusCode The HTTP status code for the redirect (default: 302 Found).
     * @return void This method terminates script execution via the `to()` method.
     */
    public static function back(string $fallbackUrl = null, int $statusCode = 302): void
    {
        // Determine the fallback URL if not explicitly provided
        if ($fallbackUrl === null) {
            if (defined('BASE_URL')) {
                $fallbackUrl = BASE_URL; // Use BASE_URL if defined
            } else {
                $fallbackUrl = '/'; // Absolute fallback if BASE_URL isn't defined
            }
        }

        // Determine the referring URL, using the fallback if HTTP_REFERER is not set.
        $referer = $_SERVER['HTTP_REFERER'] ?? $fallbackUrl;

        // Use the 'to' method to perform the actual redirect.
        // The 'to' method will handle prepending BASE_URL if $referer is root-relative.
        self::to($referer, $statusCode);
    }
}
