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
     * Clears compiled route cache when routes are reloaded.
     *
     * @param array $routes Route definitions
     * @return void
     */
    public static function load(array $routes): void
    {
        // Reset routes and cache
        self::$routes = [];
        self::$namedRoutes = [];
        self::$compiledRoutes = [];

        // Process each route definition
        foreach ($routes as $name => $definition) {
            // Validate required route fields
            if (!isset($definition['method'], $definition['path'], $definition['controller'], $definition['action'])) {
                continue;
            }

            // Add route name to definition
            $definition['name'] = $name;
            self::$routes[] = $definition;
            self::$namedRoutes[$name] = $definition;
        }
    }

    /**
     * Cache for compiled route patterns
     * @var array<string, array{pattern: string, params: array}>
     */
    private static $compiledRoutes = [];

    /**
     * Attempt to match a request to a route.
     *
     * @param string $method HTTP method
     * @param string $path Request path
     * @return array|null Matched route data or null if no match
     */
    public static function match(string $method, string $path): ?array
    {
        $method = strtoupper($method);

        // Iterate through routes
        foreach (self::$routes as $route) {
            // Skip if method doesn't match
            if ($method !== strtoupper($route['method'])) {
                continue;
            }

            // Use cached compiled pattern if available
            $routeKey = $route['name'] ?? $route['path'];
            if (!isset(self::$compiledRoutes[$routeKey])) {
                $pattern = self::compilePathToRegex($route['path'], $parameterNames);
                self::$compiledRoutes[$routeKey] = [
                    'pattern' => $pattern,
                    'params' => $parameterNames
                ];
            }

            $compiled = self::$compiledRoutes[$routeKey];
            $parameterNames = $compiled['params'];

            // Try to match path against pattern
            if (preg_match($compiled['pattern'], $path, $matches)) {
                $params = [];
                
                // Extract parameter values from matches
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

    /**
     * Compile route path to regex pattern
     * 
     * Converts route paths like "/user/{id}" to regex patterns.
     * Extracts parameter names for later use.
     *
     * @param string $path Route path with optional parameters
     * @param array|null $parameterNames Output parameter - array of parameter names
     * @return string Compiled regex pattern
     */
    private static function compilePathToRegex(string $path, ?array &$parameterNames = []): string
    {
        $parameterNames = [];

        // Replace {param} with regex capture groups
        $pattern = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            function ($matches) use (&$parameterNames) {
                $parameterNames[] = $matches[1];
                return '([^/]+)'; // Match any characters except forward slash
            },
            $path
        );

        // Return pattern with anchors
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

