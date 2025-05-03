<?php
namespace App\Core;
use App\Core\Registry; 
class Redirect
{
    public static function to(string $url, int $statusCode = 302): void
    {
        $finalUrl = $url;
        if (strpos($url, '/') === 0 && strpos($url, '
            if (defined('BASE_URL')) {
                $finalUrl = rtrim(BASE_URL, '/') . '/' . ltrim($url, '/');
            } else {
                if (Registry::has('logger')) {
                    Registry::get('logger')->error("BASE_URL constant is not defined, cannot redirect relative path.", ['url' => $url]);
                }
                http_response_code(500);
                echo "Error: Cannot redirect relative path because BASE_URL is not defined.";
                exit();
            }
        }
        elseif (strpos($finalUrl, '
            if (Registry::has('logger')) {
                Registry::get('logger')->warning("Invalid redirect URL format provided.", ['url' => $url, 'finalUrl' => $finalUrl]);
            }
            http_response_code(500);
            echo "Error: Invalid redirect URL specified.";
            exit();
        }
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Location: ' . $finalUrl, true, $statusCode);
        exit();
    }
    public static function back(string $fallbackUrl = null, int $statusCode = 302): void
    {
        if ($fallbackUrl === null) {
            if (defined('BASE_URL')) {
                $fallbackUrl = BASE_URL; 
            } else {
                $fallbackUrl = '/'; 
            }
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? $fallbackUrl;
        self::to($referer, $statusCode);
    }
}
