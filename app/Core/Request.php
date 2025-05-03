<?php

namespace App\Core;

class Request
{
    private $getParams;
    private $postParams;
    private $files;
    private $server;
    private $headers;
    private $inputStream;
    public function __construct()
    {
        $this->getParams = filter_input_array(INPUT_GET, FILTER_DEFAULT) ?? [];
        $this->postParams = filter_input_array(INPUT_POST, FILTER_DEFAULT) ?? [];
        $this->files = $_FILES;
        $this->server = $_SERVER;
        $this->headers = $this->extractHeaders();
        $this->inputStream = null;
    }
    private function extractHeaders(): array
    {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            return $headers !== false ? $headers : [];
        }
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headerKey = str_replace('_', '-', strtolower(substr($key, 5)));
                $headerKey = ucwords($headerKey, '-');
                $headers[$headerKey] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $headerKey = str_replace('_', '-', strtolower($key));
                $headerKey = ucwords($headerKey, '-');
                $headers[$headerKey] = $value;
            }
        }
        return $headers;
    }
    public function method(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }
    public function uri(): string
    {
        $baseUrlPath = defined('BASE_URL') ? BASE_URL : '/';
        $fullUri = parse_url($this->server['REQUEST_URI'] ?? '', PHP_URL_PATH);
        if ($fullUri !== null && $baseUrlPath !== '/' && strpos($fullUri, $baseUrlPath) === 0) {
            $relativePath = substr($fullUri, strlen($baseUrlPath));
        } else {
            $relativePath = $fullUri;
        }
        $originalRelativePath = $relativePath;
        if (strpos($relativePath, 'public/') === 0) {
            $relativePath = substr($relativePath, strlen('public/'));
        }
        if (defined('BASE_PATH')) {
            $logMessage = "Request URI: " . ($this->server['REQUEST_URI'] ?? 'N/A') .
                " | BASE_URL: " . $baseUrlPath .
                " | Original Relative Path: " . ($originalRelativePath ?: 'N/A') .
                " | Final Relative Path: " . ($relativePath ?: 'N/A');
            if (Registry::has('logger')) {
                Registry::get('logger')->debug($logMessage);
            } else {
                error_log($logMessage . "\n", 3, BASE_PATH . '/logs/app.log');
            }
        } else {
            error_log(
                "Request URI: " . ($this->server['REQUEST_URI'] ?? 'N/A') .
                    " | BASE_URL: " . $baseUrlPath .
                    " | Original Relative Path: " . ($originalRelativePath ?: 'N/A') .
                    " | Final Relative Path: " . ($relativePath ?: 'N/A') .
                    " | WARNING: BASE_PATH not defined, logging to default log."
            );
        }
        return trim($relativePath ?: '', '/');
    }
    public function get(string $key, $default = null)
    {
        return $this->getParams[$key] ?? $default;
    }
    public function allGet(): array
    {
        return $this->getParams;
    }
    public function post(string $key, $default = null)
    {
        return $this->postParams[$key] ?? $default;
    }
    public function allPost(): array
    {
        return $this->postParams;
    }
    public function input(string $key, $default = null)
    {
        if (isset($this->postParams[$key])) {
            return $this->postParams[$key];
        }
        return $this->getParams[$key] ?? $default;
    }
    public function allInput(): array
    {
        return array_merge($this->getParams, $this->postParams);
    }
    public function rawInput()
    {
        if ($this->inputStream === null) {
            $this->inputStream = file_get_contents('php://input');
        }
        return $this->inputStream;
    }
    public function json(bool $associative = true, int $depth = 512, int $flags = 0)
    {
        $rawInput = $this->rawInput();
        if (empty($rawInput)) {
            return null;
        }
        $decoded = json_decode($rawInput, $associative, $depth, $flags);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
    }
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }
    public function allFiles(): array
    {
        return $this->files;
    }
    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }
    public function header(string $key, $default = null): ?string
    {
        $normalizedKey = strtolower($key);
        foreach ($this->headers as $headerName => $headerValue) {
            if (strtolower($headerName) === $normalizedKey) {
                return $headerValue;
            }
        }
        return $default;
    }
    public function allHeaders(): array
    {
        return $this->headers;
    }
    public function server(string $key, $default = null)
    {
        return $this->server[$key] ?? $default;
    }
    public function ip(): ?string
    {
        return $this->server['REMOTE_ADDR'] ?? null;
    }
    public function isAjax(): bool
    {
        return strtolower($this->header('X-Requested-With', '')) === 'xmlhttprequest';
    }
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }
    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }
}