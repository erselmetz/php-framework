<?php

class Controller{

    public function view($view, $data = []){
        require_once 'public/views/'.$view.'.php';
    }

    public function model($model){
        require_once 'app/models/'.$model.'.php';
        return new $model();
    }

    public function error_404(){
        echo 'Not found 404';
    }
    
}