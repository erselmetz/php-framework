<?php

// Load core files first
require_once __DIR__ . '/core/env.php';

use Core\Env;

// Load env if not already loaded
if (class_exists('Core\Env')) {
    Env::load();
}

$RewriteBase = Env::get('APP_URL', '/php-framework/');

// MySQL Database
$database = [
    "host" => Env::get('DB_HOST', 'localhost'),
    "username" => Env::get('DB_USERNAME', 'root'),
    "password" => Env::get('DB_PASSWORD', ''),
    "dbname" => Env::get('DB_DATABASE', 'test')
];

// SQLite Database
$SQLite = Env::get('SQLITE_FILE', 'test.sqlite');