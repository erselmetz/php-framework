<?php

namespace Core;

class Logger
{
    private static $logPath = null;

    public static function setPath(string $path): void
    {
        self::$logPath = $path;
    }

    public static function getPath(): string
    {
        if (self::$logPath === null) {
            self::$logPath = dirname(__DIR__) . '/storage/logs';
        }

        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }

        return self::$logPath;
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = "[{$timestamp}] {$level}: {$message}{$contextString}" . PHP_EOL;

        $logFile = self::getPath() . '/' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        self::log('DEBUG', $message, $context);
    }
}

