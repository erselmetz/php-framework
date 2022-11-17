<?php

class Controller{

    public function view($view, $data = []){

        require_once 'app/views/'.$view.'.php';

    }

    public function error_404(){

        echo 'Not found 404';

    }

    public function is_auth(){

        if(Auth::user() == false){

            echo header("location: login");
            
        }    

    }
    
}