<?php

use Core\Router;
use Core\Session;
use Core\Middleware;
use Core\View;
use Core\Logger;

/**
 * Generate URL for public assets with cache busting
 * 
 * @param string $params Asset path relative to public directory
 * @return string Full URL to the asset with version parameter
 */
function assets(string $params): string
{
    global $RewriteBase;

    // Normalize base path
    $base = ($RewriteBase ?? '/');
    $basePath = rtrim($base, '/') . '/public/';
    $filePath = dirname(__DIR__) . '/public/' . $params;
    $url = $basePath . $params;

    // Add cache busting parameter if file exists
    if (file_exists($filePath)) {
        $separator = strpos($url, '?') === false ? '?' : '&';
        $url .= $separator . 'v=' . filemtime($filePath);
    }

    return $url;
}

function route(string $name, array $params = []): ?string
{
    return Router::url($name, $params);
}

function flash(string $key, $value = null)
{
    if ($value === null) {
        return Session::getFlash($key);
    }

    Session::flash($key, $value);
    return null;
}

function env(string $key, $default = null)
{
    return \Core\Env::get($key, $default);
}

function csrf_token(): string
{
    return \Core\CSRF::token();
}

function csrf_field(): string
{
    return \Core\CSRF::field();
}

function cache(string $key, $default = null)
{
    return \Core\Cache::get($key, $default);
}

function cache_put(string $key, $value, int $ttl = null): bool
{
    return \Core\Cache::put($key, $value, $ttl);
}

function cache_remember(string $key, callable $callback, int $ttl = null)
{
    return \Core\Cache::remember($key, $callback, $ttl);
}

/**
 * Post Data Handler
 * 
 * Provides a fluent interface for sanitizing and validating POST data.
 * Note: Uses static properties which may cause issues in concurrent requests.
 * Consider refactoring to instance-based approach for production use.
 */
class Post
{
    /** @var string|null Sanitized POST value */
    private static $var = null;
    
    /** @var int|null Maximum length limit for the value */
    private static $limit = null;

    /**
     * Require and sanitize a POST parameter
     * 
     * @param string $params POST parameter name
     * @return self For method chaining
     */
    public static function require(string $params): self
    {
        // Reset previous value
        Post::$var = null;

        if (isset($_POST[$params])) {
            $value = $_POST[$params];

            // Only process non-empty values
            if ($value !== null && $value !== '') {
                $value = trim((string) $value);
                // Sanitize: strip tags and escape HTML
                $sanitized = htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
                Post::$var = $sanitized;
            }
        }
        return new Post;
    }

    /**
     * Set maximum length limit for the value
     * 
     * @param mixed $params Maximum length (must be numeric)
     * @return self For method chaining
     */
    public static function limit($params): self
    {
        if (is_numeric($params)) {
            Post::$limit = max(0, (int) $params);
        }
        return new Post;
    }

    /**
     * Get the sanitized value
     * 
     * @return string|null Sanitized value or null if not set
     */
    public static function get(): ?string
    {
        if (Post::$var === null) {
            return null;
        }

        // Check length limit if set
        if (Post::$limit !== null && strlen(Post::$var) > Post::$limit) {
            $message = "value is limited only to " . Post::$limit;
            Post::$var = $message;
        }

        $value = Post::$var;
        // Reset limit for next use
        Post::$limit = null;

        return $value;
    }
}

/**
 * Authentication Helper
 * 
 * Provides simple authentication checks based on session data.
 */
class Auth
{
    /**
     * Check if user is authenticated
     * 
     * @return bool True if user has email and password in session
     */
    public static function user(): bool
    {
        return Session::has('email') && Session::has('password');
    }
}

/**
 * Application Core
 * 
 * Handles routing, controller loading, and request dispatching.
 */
class App
{
    /** @var string Default controller name */
    protected $controller = 'html';
    
    /** @var string Default method name */
    protected $method = 'index';
    
    /** @var array Request parameters */
    protected $params = [];

    /**
     * Initialize application and handle request
     * 
     * @throws \Exception If controller file not found or method doesn't exist
     */
    public function __construct()
    {
        try {
            // Load routes configuration
            $routesFile = 'routes.php';
            $routes = file_exists($routesFile) ? require $routesFile : [];
            Router::load($routes);

            // Parse URL and determine request path
            $segments = $this->parseUrl();
            $requestPath = $this->buildPathFromSegments($segments);
            $httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

            // Try to match route
            $routeMatch = Router::match($httpMethod, $requestPath);
            $routeMatched = $routeMatch !== null;
            $pendingSegments = $segments;

            // Determine controller
            if ($routeMatched) {
                $this->controller = $routeMatch['controller'];
            } else {
                // Fallback to segment-based routing
                if (empty($pendingSegments)) {
                    $pendingSegments = [$this->controller];
                }

                $controllerFile = 'app/controllers/' . $pendingSegments[0] . '.php';
                if (isset($pendingSegments[0]) && file_exists($controllerFile)) {
                    $this->controller = $pendingSegments[0];
                    unset($pendingSegments[0]);
                    $pendingSegments = array_values($pendingSegments);
                }
            }

            // Load and instantiate controller
            $controllerFile = 'app/controllers/' . $this->controller . '.php';
            if (!file_exists($controllerFile)) {
                Logger::error("Controller file not found: {$controllerFile}", [
                    'controller' => $this->controller,
                    'path' => $requestPath
                ]);
                throw new \Exception("Controller '{$this->controller}' not found");
            }

            require_once $controllerFile;
            $controllerClass = $this->controller;
            
            if (!class_exists($controllerClass)) {
                Logger::error("Controller class not found: {$controllerClass}", [
                    'file' => $controllerFile
                ]);
                throw new \Exception("Controller class '{$controllerClass}' not found");
            }

            $this->controller = new $controllerClass;

            // Determine method and parameters
            if ($routeMatched) {
                $this->method = $routeMatch['action'];
                $this->params = array_values($routeMatch['params']);
            } else {
                $this->method = $this->resolveMethodFromSegments($this->controller, $pendingSegments, $this->method);
                $this->params = $pendingSegments ? array_values($pendingSegments) : [];
            }

            // Check if method exists
            if (!method_exists($this->controller, $this->method)) {
                Logger::error("Method not found in controller", [
                    'controller' => get_class($this->controller),
                    'method' => $this->method,
                    'path' => $requestPath
                ]);
                throw new \Exception("Method '{$this->method}' not found in controller");
            }

            // Apply middleware
            $middlewareStack = $routeMatch['middleware'] ?? [];
            $this->applyMiddleware($middlewareStack);

            // Execute controller method
            call_user_func_array([$this->controller, $this->method], [$this->params]);

        } catch (\Exception $e) {
            Logger::error("Application error: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Show error page or re-throw in debug mode
            if (env('APP_DEBUG', false)) {
                throw $e;
            }
            
            http_response_code(500);
            die('An error occurred. Please check the logs.');
        }
    }

    /**
     * Parse URL from GET parameter
     * 
     * @return array URL segments as array
     */
    public function parseUrl(): array
    {
        if (!isset($_GET['url'])) {
            return [];
        }

        // Remove trailing slashes
        $trimmed = rtrim($_GET['url'], '/');
        if ($trimmed === '') {
            return [];
        }

        // Sanitize URL to prevent injection
        $sanitized = filter_var($trimmed, FILTER_SANITIZE_URL);

        if ($sanitized === '' || $sanitized === false) {
            Logger::warning("Invalid URL sanitized", ['original' => $trimmed]);
            return [];
        }

        return explode('/', $sanitized);
    }

    /**
     * Build request path from URL segments
     * 
     * @param array $segments URL segments
     * @return string Request path
     */
    private function buildPathFromSegments(array $segments): string
    {
        if (empty($segments)) {
            return '/';
        }

        return '/' . implode('/', $segments);
    }

    /**
     * Resolve controller method from URL segments
     * 
     * @param object $controllerInstance Controller instance
     * @param array $segments URL segments (passed by reference, modified)
     * @param string $defaultMethod Default method name
     * @return string Resolved method name
     */
    private function resolveMethodFromSegments($controllerInstance, array &$segments, string $defaultMethod): string
    {
        $method = $defaultMethod;

        // Check first two segments for method name (max depth: 2)
        for ($i = 0; $i < 2; $i++) {
            if (isset($segments[$i])) {
                if (method_exists($controllerInstance, $segments[$i])) {
                    $method = $segments[$i];
                    unset($segments[$i]);
                    break; // Found method, no need to continue
                }
            }
        }

        return $method;
    }

    /**
     * Apply middleware stack
     * 
     * @param array $routeMiddleware Middleware from route definition
     * @return void
     */
    private function applyMiddleware(array $routeMiddleware = []): void
    {
        // Get middleware from controller if available
        $controllerMiddleware = method_exists($this->controller, 'getMiddleware')
            ? $this->controller->getMiddleware()
            : [];

        // Merge route and controller middleware
        $stack = array_merge((array) $routeMiddleware, (array) $controllerMiddleware);

        // Execute middleware stack
        Middleware::runStack($stack, $this->method, [
            'controller' => $this->controller,
            'params' => $this->params,
            'route' => $this->method,
        ]);
    }
}