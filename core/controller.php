<?php

use Core\View;
use Core\Validator;
use Core\Response;
use Core\Session;
use Core\CSRF;

class Controller{

    protected $layout = null;
    protected $middleware = [];

    public function view($view, $data = [], $layout = null){

        $selectedLayout = $layout ?? ($data['layout'] ?? $this->layout);
        if(isset($data['layout'])){
            unset($data['layout']);
        }

        View::render($view, $data, $selectedLayout);

    }

    public function error_404(){

        echo 'Not found 404';

    }

    public function is_auth(){

        if(Auth::user() == false){

            $loginRoute = route('login.show') ?? 'login';
            header("location: ".$loginRoute);
            exit;
            
        }

    }

    public function getMiddleware(): array
    {
        if (method_exists($this, 'middleware')) {
            $result = $this->middleware();
            if (is_array($result)) {
                return $result;
            }
        }

        return (array) $this->middleware;
    }

    protected function validate(array $data, array $rules): Validator
    {
        return Validator::make($data, $rules);
    }

    protected function json($data, int $statusCode = 200): void
    {
        Response::json($data, $statusCode);
    }

    protected function success($data = null, string $message = 'Success', int $statusCode = 200): void
    {
        Response::success($data, $message, $statusCode);
    }

    protected function error(string $message, int $statusCode = 400, $errors = null): void
    {
        Response::error($message, $statusCode, $errors);
    }

    protected function redirect(string $url, int $statusCode = 302): void
    {
        Response::redirect($url, $statusCode);
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        Response::redirect($referer);
    }

    protected function csrfToken(): string
    {
        return CSRF::token();
    }

    protected function csrfField(): string
    {
        return CSRF::field();
    }
    
}