<?php

namespace App\Helpers;

class SecurityHelper
{
    public static function sanitizeInput(?string $data): string
    {
        if ($data === null) {
            return '';
        }
        $data = trim($data);
        $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return $data;
    }
    public static function encodeOutput(?string $data): string
    {
        if ($data === null) {
            return '';
        }
        return htmlentities($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    public static function validatePhone(string $phone): bool
    {
        $phone = preg_replace('/[\s\-\(\)]+/', '', $phone);
        return preg_match('/^\d{10}$/', $phone) === 1;
    }
    public static function validateName(string $name): bool
    {
        return preg_match('/^[\p{L}\s\'\-]+$/u', $name) === 1;
    }
    public static function validatePassword(string $password): bool
    {
        if (mb_strlen($password) < 8) {
            return false;
        }
        return true;
    }
    public static function generateToken(int $length = 64): string
    {
        if ($length % 2 !== 0) {
            throw new \InvalidArgumentException("Token length must be an even number.");
        }
        $byteLength = $length / 2;
        $randomBytes = random_bytes($byteLength);
        return bin2hex($randomBytes);
    }
    public static function preventClickjacking(): void
    {
        if (!headers_sent()) {
            header('X-Frame-Options: DENY');
        }
    }
    public static function setSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        $csp = "default-src 'self'; ";
        $csp .= "script-src 'self' https://cdnjs.cloudflare.com https://unpkg.com 'unsafe-inline'; ";
        $csp .= "style-src 'self' https://cdnjs.cloudflare.com https://fonts.googleapis.com 'unsafe-inline'; ";
        $csp .= "img-src 'self' data:; ";
        $csp .= "font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; ";
        $connectSrcBase = BASE_URL;
        $publicSuffix = '/public/';
        if (substr($connectSrcBase, -strlen($publicSuffix)) === $publicSuffix) {
            $connectSrcBase = substr($connectSrcBase, 0, -strlen($publicSuffix));
        }
        $connectSrcBasePath = rtrim($connectSrcBase, '/') . '/';
        $csp .= "connect-src 'self' " . $connectSrcBasePath . "; ";
        $csp .= "form-action 'self'; ";
        $csp .= "frame-ancestors 'none'; ";
        $csp .= "base-uri 'self'; ";
        $csp .= "object-src 'none';";
        header("Content-Security-Policy: " . $csp);
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
    public static function logSecurityEvent(string $eventType, string $message, array $context = []): void
    {
        $logDirectory = defined('BASE_PATH') ? BASE_PATH . '/logs' : __DIR__ . '/../../logs';
        $logFile = $logDirectory . '/security.log';
        if (!is_dir($logDirectory)) {
            @mkdir($logDirectory, 0755, true);
        }
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $log_message = "[{$timestamp}] [{$eventType}] [IP: {$ip}] {$message}";
        if (!empty($context)) {
            $log_message .= ' | Context: ' . json_encode($context, JSON_UNESCAPED_SLASHES);
        }
        @error_log($log_message . PHP_EOL, 3, $logFile);
    }
    public static function generateRandomString(int $length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}