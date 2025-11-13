<?php

return [
    'home' => [
        'method' => 'GET',
        'path' => '/',
        'controller' => 'html',
        'action' => 'index',
    ],
    'login.show' => [
        'method' => 'GET',
        'path' => '/login',
        'controller' => 'html',
        'action' => 'login',
    ],
];

