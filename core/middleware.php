<?php

namespace Core;

class Middleware
{
    /**
     * @var array<string, callable>
     */
    private static $registry = [];

    public static function register(string $name, callable $handler): void
    {
        self::$registry[$name] = $handler;
    }

    public static function exists(string $name): bool
    {
        return isset(self::$registry[$name]);
    }

    /**
     * Execute a middleware by name.
     *
     * @param string $name
     * @param array $context
     * @return void
     */
    public static function run(string $name, array $context = []): void
    {
        if (!self::exists($name)) {
            return;
        }

        call_user_func(self::$registry[$name], $context);
    }

    /**
     * Run a stack of middleware definitions.
     *
     * @param array $definitions
     * @param string $method
     * @param array $context
     * @return void
     */
    public static function runStack(array $definitions, string $method, array $context = []): void
    {
        foreach ($definitions as $definition) {
            $config = self::normaliseDefinition($definition);

            if ($config === null) {
                continue;
            }

            if (!self::shouldApply($config, $method)) {
                continue;
            }

            self::run($config['name'], $context);
        }
    }

    private static function normaliseDefinition($definition): ?array
    {
        if (is_string($definition)) {
            return ['name' => $definition];
        }

        if (is_array($definition) && isset($definition['name'])) {
            $definition['only'] = isset($definition['only']) ? (array) $definition['only'] : [];
            $definition['except'] = isset($definition['except']) ? (array) $definition['except'] : [];
            return $definition;
        }

        return null;
    }

    private static function shouldApply(array $definition, string $method): bool
    {
        if (!empty($definition['only']) && !in_array($method, $definition['only'], true)) {
            return false;
        }

        if (!empty($definition['except']) && in_array($method, $definition['except'], true)) {
            return false;
        }

        return true;
    }
}

