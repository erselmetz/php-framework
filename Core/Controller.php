<?php

class Controller{

    public function view($view, $data = []){
        require_once 'App/Views/'.$view.'.php';
    }

    public function model($model){
        require_once 'App/Models/'.$model.'.php';
        return new $model();
    }

    public function error_404(){
        echo 'Not found 404';
    }
    
}