<?php

namespace App\Core;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LogLevel;
use Exception;

class Registry
{
    private static $services = [];
    public static function bind(string $key, $value, bool $overwrite = false): void
    {
        if (!$overwrite && static::has($key)) {
            throw new Exception("Key '{$key}' is already bound in the registry.");
        }
        static::$services[$key] = $value;
    }
    public static function get(string $key)
    {
        if (!static::has($key)) {
            throw new Exception("No service bound for key '{$key}' in the registry.");
        }
        return static::$services[$key];
    }
    public static function has(string $key): bool
    {
        return isset(static::$services[$key]);
    }
    public static function remove(string $key): void
    {
        unset(static::$services[$key]);
    }
    public static function flush(): void
    {
        static::$services = [];
    }
    public static function initializeLogger(string $logFilePath, string $loggerName = 'app', int $logLevel = LogLevel::DEBUG): void
    {
        try {
            $logDir = dirname($logFilePath);
            if (!is_dir($logDir) && !mkdir($logDir, 0775, true)) {
                throw new Exception("Failed to create log directory: {$logDir}");
            }
            $logger = new Logger($loggerName);
            $handler = new StreamHandler($logFilePath, $logLevel);
            $outputFormat = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
            $dateFormat = "Y-m-d H:i:s";
            $formatter = new LineFormatter($outputFormat, $dateFormat, true, true);
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
            static::bind('logger', $logger, true);
        } catch (Exception $e) {
            throw new Exception("Failed to initialize logger: " . $e->getMessage(), 0, $e);
        }
    }
}