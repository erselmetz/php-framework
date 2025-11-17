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
    'form.show' => [
        'method' => 'GET',
        'path' => '/form',
        'controller' => 'form',
        'action' => 'show',
    ],
    'form.submit' => [
        'method' => 'POST',
        'path' => '/form/submit',
        'controller' => 'form',
        'action' => 'submit',
    ],
    'api.users' => [
        'method' => 'GET',
        'path' => '/api/users',
        'controller' => 'api',
        'action' => 'users',
    ],
    'api.store' => [
        'method' => 'POST',
        'path' => '/api/store',
        'controller' => 'api',
        'action' => 'store',
    ],
    'api.upload' => [
        'method' => 'POST',
        'path' => '/api/upload',
        'controller' => 'api',
        'action' => 'upload',
    ],
    'api.cached' => [
        'method' => 'GET',
        'path' => '/api/cached',
        'controller' => 'api',
        'action' => 'cached',
    ],
];

