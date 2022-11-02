<?php

require_once "Core/Config.php";

// database
require_once 'Core/Database.php';

// autoload model
spl_autoload_register(function($classname){
    require_once 'App/Models/'.$classname.'.php';
});

require_once 'Core/App.php';
require_once 'Core/Controller.php';