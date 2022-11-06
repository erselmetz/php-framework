<?php

require_once "core/config.php";

// database
require_once 'core/Database.php';

// autoload model
spl_autoload_register(function($classname){
    require_once 'app/Models/'.$classname.'.php';
});

require_once 'core/app.php';
require_once 'core/controller.php';