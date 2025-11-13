<?php

use Core\Session;
use Core\Router;
use Core\Middleware;

// database
require_once 'core/database.php';
require_once 'core/session.php';
require_once 'core/router.php';
require_once 'core/middleware.php';
require_once 'core/view.php';

Session::start();

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