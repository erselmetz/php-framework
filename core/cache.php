<?php

namespace Core;

class Cache
{
    private static $cachePath = null;
    private static $defaultTtl = 3600; // 1 hour

    public static function setPath(string $path): void
    {
        self::$cachePath = $path;
    }

    public static function getPath(): string
    {
        if (self::$cachePath === null) {
            self::$cachePath = dirname(__DIR__) . '/storage/cache';
        }

        if (!is_dir(self::$cachePath)) {
            mkdir(self::$cachePath, 0755, true);
        }

        return self::$cachePath;
    }

    public static function get(string $key, $default = null)
    {
        $file = self::getPath() . '/' . md5($key) . '.cache';

        if (!file_exists($file)) {
            return $default;
        }

        $data = unserialize(file_get_contents($file));

        if ($data['expires'] < time()) {
            unlink($file);
            return $default;
        }

        return $data['value'];
    }

    public static function put(string $key, $value, int $ttl = null): bool
    {
        $ttl = $ttl ?? self::$defaultTtl;
        $file = self::getPath() . '/' . md5($key) . '.cache';

        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];

        return file_put_contents($file, serialize($data), LOCK_EX) !== false;
    }

    public static function remember(string $key, callable $callback, int $ttl = null)
    {
        $value = self::get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        self::put($key, $value, $ttl);

        return $value;
    }

    public static function forget(string $key): bool
    {
        $file = self::getPath() . '/' . md5($key) . '.cache';

        if (file_exists($file)) {
            return unlink($file);
        }

        return false;
    }

    public static function flush(): bool
    {
        $path = self::getPath();
        $files = glob($path . '/*.cache');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    public static function has(string $key): bool
    {
        return self::get($key) !== null;
    }
}

