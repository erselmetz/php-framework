<?php

use Core\Router;
use Core\Session;
use Core\Middleware;
use Core\View;

function assets($params){

    global $RewriteBase;

    $basePath = rtrim($RewriteBase, '/').'/public/';
    $filePath = dirname(__DIR__).'/public/'.$params;
    $url = $basePath.$params;

    if (file_exists($filePath)) {
        $separator = strpos($url, '?') === false ? '?' : '&';
        $url .= $separator.'v='.filemtime($filePath);
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

class Post{

    private static $var = null;
    private static $limit = null;

    public static function require($params){

        Post::$var = null;

        if(isset($_POST[$params])){

            $value = $_POST[$params];

            if($value !== null && $value !== ''){

                $value = trim((string) $value);
                $sanitized = htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
                Post::$var = $sanitized;

            }
        }
        return new Post;

    }

    public static function limit($params){

        if(is_numeric($params)){

            Post::$limit = max(0, (int) $params);

        }
        return new Post;
    }

    public static function get(){

        if(Post::$var === null){
            return null;
        }

        if(Post::$limit !== null && strlen(Post::$var) > Post::$limit){

            $message = "value is limited only to ".Post::$limit;
            Post::$var = $message;

        }

        $value = Post::$var;
        Post::$limit = null;

        return $value;
    }
}

class Auth{

    public static function user(){
        
        return Session::has('email') && Session::has('password');
    }
}

class App{

    protected $controller = 'html';
    protected $method = 'index';
    protected $params = [];

    public function __construct(){

        global $post;

        $routes = file_exists('routes.php') ? require 'routes.php' : [];
        Router::load($routes);

        $segments = $this->parseUrl();
        $requestPath = $this->buildPathFromSegments($segments);
        $httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $routeMatch = Router::match($httpMethod, $requestPath);

        $routeMatched = $routeMatch !== null;
        $pendingSegments = $segments;

        if($routeMatched){
            $this->controller = $routeMatch['controller'];
        }else{
            if(empty($pendingSegments)){
                $pendingSegments = [$this->controller];
            }

            if(isset($pendingSegments[0]) && file_exists('app/controllers/'.$pendingSegments[0].'.php')){

                $this->controller = $pendingSegments[0];
                unset($pendingSegments[0]);
                $pendingSegments = array_values($pendingSegments);
                
            }
        }

        require_once 'app/controllers/'.$this->controller.'.php';

        $controllerClass = $this->controller;
        $this->controller = new $controllerClass;

        if($routeMatched){
            $this->method = $routeMatch['action'];
            $this->params = array_values($routeMatch['params']);
        }else{
            $this->method = $this->resolveMethodFromSegments($this->controller, $pendingSegments, $this->method);
            $this->params = $pendingSegments ? array_values($pendingSegments) : [];
        }

        $middlewareStack = $routeMatch['middleware'] ?? [];
        $this->applyMiddleware($middlewareStack);

        call_user_func_array([$this->controller, $this->method], [$this->params]);

    }

    public function parseUrl(){

        if(!isset($_GET['url'])){
            return [];
        }

        $trimmed = rtrim($_GET['url'], '/');
        if($trimmed === ''){
            return [];
        }

        $sanitized = filter_var($trimmed, FILTER_SANITIZE_URL);

        if($sanitized === '' || $sanitized === false){
            return [];
        }

        return explode('/', $sanitized);
    }

    private function buildPathFromSegments(array $segments): string
    {
        if(empty($segments)){
            return '/';
        }

        return '/'.implode('/', $segments);
    }

    private function resolveMethodFromSegments($controllerInstance, array &$segments, string $defaultMethod): string
    {
        $method = $defaultMethod;

        for($i = 0; $i < 2; $i++){
            if(isset($segments[$i])){
                if(method_exists($controllerInstance, $segments[$i])){
                    $method = $segments[$i];
                    unset($segments[$i]);
                }
            }
        }

        return $method;
    }

    private function applyMiddleware(array $routeMiddleware = []): void
    {
        $controllerMiddleware = method_exists($this->controller, 'getMiddleware')
            ? $this->controller->getMiddleware()
            : [];

        $stack = array_merge((array) $routeMiddleware, (array) $controllerMiddleware);

        Middleware::runStack($stack, $this->method, [
            'controller' => $this->controller,
            'params' => $this->params,
            'route' => $this->method,
        ]);
    }
}