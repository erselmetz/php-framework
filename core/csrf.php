<?php

namespace Core;

use Core\Session;

class CSRF
{
    public static function token(): string
    {
        if (!Session::has('_csrf_token')) {
            Session::set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('_csrf_token');
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . self::token() . '">';
    }

    public static function verify(?string $token = null): bool
    {
        $token = $token ?? $_POST['_token'] ?? $_GET['_token'] ?? null;
        
        if ($token === null) {
            return false;
        }

        $sessionToken = Session::get('_csrf_token');
        
        if ($sessionToken === null) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    public static function validate(): void
    {
        if (!self::verify()) {
            http_response_code(419);
            die('CSRF token mismatch.');
        }
    }
}

