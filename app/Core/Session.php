<?php

namespace App\Core;

use App\Core\Registry;

class Session
{
    private $config = [
        'cookie_lifetime' => 0,
        'cookie_path' => '/',
        'cookie_domain' => '',
        'cookie_secure' => false,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'session_timeout' => 1800,
        'regenerate_interval' => 300,
        'check_ip_address' => true,
        'csrf_token_key' => '_csrf_token',
        'flash_message_key' => '_flash',
        'user_id_key' => 'user_id',
        'login_time_key' => 'login_time',
        'user_ip_key' => 'user_ip',
    ];
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->config['cookie_secure'] = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        if (empty($this->config['cookie_domain'])) {
            $this->config['cookie_domain'] = $_SERVER['HTTP_HOST'] ?? '';
        }
    }
    public function start(): bool
    {
        if ($this->isActive()) {
            return true;
        }
        session_set_cookie_params([
            'lifetime' => $this->config['cookie_lifetime'],
            'path' => $this->config['cookie_path'],
            'domain' => $this->config['cookie_domain'],
            'secure' => $this->config['cookie_secure'],
            'httponly' => $this->config['cookie_httponly'],
            'samesite' => $this->config['cookie_samesite']
        ]);
        if (session_start()) {
            $this->validateActivity();
            return true;
        }
        return false;
    }
    public function isActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }
    public function regenerate(bool $deleteOldSession = true): void
    {
        if ($this->isActive()) {
            $this->set('_last_regenerate', time());
            session_regenerate_id($deleteOldSession);
        }
    }
    public function destroy(): void
    {
        if ($this->isActive()) {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            session_destroy();
        }
    }
    public function flash(string $key, $value): void
    {
        $flashKey = $this->config['flash_message_key'];
        if (!isset($_SESSION[$flashKey])) {
            $_SESSION[$flashKey] = [];
        }
        $_SESSION[$flashKey][$key] = $value;
    }
    public function getFlash(string $key, $default = null)
    {
        $flashKey = $this->config['flash_message_key'];
        $value = $_SESSION[$flashKey][$key] ?? $default;
        if (isset($_SESSION[$flashKey][$key])) {
            unset($_SESSION[$flashKey][$key]);
            if (empty($_SESSION[$flashKey])) {
                unset($_SESSION[$flashKey]);
            }
        }
        return $value;
    }
    public function hasFlash(string $key): bool
    {
        $flashKey = $this->config['flash_message_key'];
        return isset($_SESSION[$flashKey][$key]);
    }
    public function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->set($this->config['csrf_token_key'], $token);
        return $token;
    }
    public function getCsrfToken(): string
    {
        $key = $this->config['csrf_token_key'];
        if (!$this->has($key)) {
            return $this->generateCsrfToken();
        }
        return $this->get($key);
    }
    public function validateCsrfToken(?string $submittedToken): bool
    {
        $sessionToken = $this->get($this->config['csrf_token_key']);
        if (!$submittedToken || !$sessionToken) {
            return false;
        }
        return hash_equals($sessionToken, $submittedToken);
    }
    public function verifyCsrfToken(?string $submittedToken): bool
    {
        return $this->validateCsrfToken($submittedToken);
    }
    public function loginUser($userId): void
    {
        $this->regenerate(true);
        $this->set($this->config['user_id_key'], $userId);
        $this->set($this->config['login_time_key'], time());
        if ($this->config['check_ip_address']) {
            $this->set($this->config['user_ip_key'], $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        }
        $this->remove($this->config['csrf_token_key']);
        $this->generateCsrfToken();
    }
    public function logoutUser(): void
    {
        $flashData = $_SESSION[$this->config['flash_message_key']] ?? [];
        $this->destroy();
        $this->start();
        if (!empty($flashData)) {
            $_SESSION[$this->config['flash_message_key']] = $flashData;
        }
        $this->generateCsrfToken();
    }
    public function isAuthenticated(): bool
    {
        return $this->has($this->config['user_id_key']);
    }
    public function getUserId()
    {
        return $this->get($this->config['user_id_key']);
    }
    public static function isAuthenticatedStatic(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        return isset($_SESSION['user_id']);
    }
    public static function getStatic(string $key, $default = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        return $_SESSION[$key] ?? $default;
    }
    public static function setStatic(string $key, $value): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION[$key] = $value;
    }
    public function validateActivity(): bool
    {
        if (!$this->isAuthenticated()) {
            return true;
        }
        $loginTime = $this->get($this->config['login_time_key']);
        if ($loginTime && (time() - $loginTime > $this->config['session_timeout'])) {
            $this->flash('error', 'Your session has expired due to inactivity. Please login again.');
            $this->logoutUser();
            return false;
        }
        if ($this->config['check_ip_address']) {
            $storedIp = $this->get($this->config['user_ip_key']);
            $currentIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            if ($storedIp && $storedIp !== $currentIp) {
                if (Registry::has('logger')) {
                    Registry::get('logger')->warning("Session IP mismatch detected.", [
                        'stored_ip' => $storedIp,
                        'current_ip' => $currentIp,
                        'user_id' => $this->getUserId()
                    ]);
                }
                $this->flash('error', 'Your session is invalid due to a security check. Please login again.');
                $this->logoutUser();
                return false;
            }
            if (!$storedIp && $currentIp !== 'unknown') {
                $this->set($this->config['user_ip_key'], $currentIp);
            }
        }
        $lastRegenerate = $this->get('_last_regenerate', 0);
        if (time() - $lastRegenerate > $this->config['regenerate_interval']) {
            $this->regenerate(false);
        }
        $this->set($this->config['login_time_key'], time());
        return true;
    }
    public function requireLogin(string $redirectUrl = '/login'): void
    {
        if (!$this->isAuthenticated()) {
            $this->flash('error', 'Please login to access this page.');
            \App\Core\Redirect::to($redirectUrl);
        }
        if (!$this->validateActivity()) {
            \App\Core\Redirect::to($redirectUrl);
        }
    }
}