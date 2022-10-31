<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule();

$capsule->addConnection([
    'driver'=> $database['driver'],
    'host'=> $database['host'],
    'username'=> $database['username'],
    'password'=> $database['password'],
    'database'=> $database['databsae'],
    'charset'=> $database['charset'],
    'collation'=> $database['collation'],
    'prefix'=> $database['prefix'],
]);

$capsule->bootEloquent();