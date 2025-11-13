<?php

namespace Core;

class Router
{
    /**
     * @var array<string, array{method:string,path:string,controller:string,action:string,middleware?:array}>
     */
    private static $routes = [];

    /**
     * @var array<string, array{method:string,path:string,controller:string,action:string,middleware?:array}>
     */
    private static $namedRoutes = [];

    /**
     * Load a routes map into the router.
     *
     * @param array $routes
     * @return void
     */
    public static function load(array $routes): void
    {
        self::$routes = [];
        self::$namedRoutes = [];

        foreach ($routes as $name => $definition) {
            if (!isset($definition['method'], $definition['path'], $definition['controller'], $definition['action'])) {
                continue;
            }

            $definition['name'] = $name;
            self::$routes[] = $definition;
            self::$namedRoutes[$name] = $definition;
        }
    }

    /**
     * Attempt to match a request to a route.
     *
     * @param string $method
     * @param string $path
     * @return array|null
     */
    public static function match(string $method, string $path): ?array
    {
        $method = strtoupper($method);

        foreach (self::$routes as $route) {
            if ($method !== strtoupper($route['method'])) {
                continue;
            }

            $pattern = self::compilePathToRegex($route['path'], $parameterNames);

            if (preg_match($pattern, $path, $matches)) {
                $params = [];
                if (!empty($parameterNames)) {
                    foreach ($parameterNames as $index => $name) {
                        $params[$name] = $matches[$index + 1] ?? null;
                    }
                }

                return [
                    'name' => $route['name'],
                    'controller' => $route['controller'],
                    'action' => $route['action'],
                    'params' => $params,
                    'middleware' => $route['middleware'] ?? [],
                ];
            }
        }

        return null;
    }

    /**
     * Generate a URL from a named route.
     *
     * @param string $name
     * @param array $params
     * @return string|null
     */
    public static function url(string $name, array $params = []): ?string
    {
        if (!isset(self::$namedRoutes[$name])) {
            return null;
        }

        $path = self::$namedRoutes[$name]['path'];

        foreach ($params as $key => $value) {
            $path = str_replace('{'.$key.'}', rawurlencode((string) $value), $path);
        }

        // Remove unresolved parameters
        $path = preg_replace('/\{[^\}]+\}/', '', $path);

        $path = preg_replace('#//+#', '/', $path);
        if ($path === '') {
            $path = '/';
        }

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        return self::applyRewriteBase($path);
    }

    private static function compilePathToRegex(string $path, ?array &$parameterNames = []): string
    {
        $parameterNames = [];

        $pattern = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function ($matches) use (&$parameterNames) {
            $parameterNames[] = $matches[1];
            return '([^/]+)';
        }, $path);

        return '#^' . $pattern . '$#';
    }

    private static function applyRewriteBase(string $path): string
    {
        global $RewriteBase;

        $base = rtrim($RewriteBase ?? '/', '/');
        if ($base === '') {
            $base = '/';
        }

        if ($base === '/') {
            return $path;
        }

        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}

