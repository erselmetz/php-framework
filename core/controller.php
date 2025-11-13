<?php

use Core\View;

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
    
}