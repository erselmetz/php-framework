<?php

use Core\Session;
use Core\Router;
use Core\Middleware;
use Core\Env;
use Core\CSRF;
use Core\Logger;

// Load environment variables
require_once 'core/env.php';
Env::load();

// database
require_once 'core/database.php';
require_once 'core/session.php';
require_once 'core/router.php';
require_once 'core/middleware.php';
require_once 'core/view.php';
require_once 'core/validator.php';
require_once 'core/csrf.php';
require_once 'core/response.php';
require_once 'core/upload.php';
require_once 'core/cache.php';
require_once 'core/logger.php';

Session::start();

// Set error handler
set_error_handler(function($severity, $message, $file, $line) {
    Logger::error("PHP Error: {$message}", [
        'file' => $file,
        'line' => $line,
        'severity' => $severity
    ]);
    
    if (Env::get('APP_DEBUG', false)) {
        echo "<pre>Error: {$message}\nFile: {$file}\nLine: {$line}</pre>";
    }
});

set_exception_handler(function($exception) {
    Logger::error("Uncaught Exception: " . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    
    if (Env::get('APP_DEBUG', false)) {
        echo "<pre>Exception: " . $exception->getMessage() . "\n";
        echo "File: " . $exception->getFile() . "\n";
        echo "Line: " . $exception->getLine() . "\n";
        echo "Trace:\n" . $exception->getTraceAsString() . "</pre>";
    } else {
        http_response_code(500);
        die('An error occurred. Please try again later.');
    }
});

// autoload model
spl_autoload_register(function($classname){
    require_once 'app/models/'.$classname.'.php';
});

require_once 'core/app.php';
require_once 'core/controller.php';

// Register default middleware
Middleware::register('auth', function () {
    if (!Auth::user()) {
        $loginRoute = route('login.show') ?? 'login';
        header('Location: '.$loginRoute);
        exit;
    }
});

Middleware::register('csrf', function () {
    if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'])) {
        CSRF::validate();
    }
});