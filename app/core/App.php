<?php

function assets($params){
    global $RewriteBase;
    return $RewriteBase.'public/'.$params;
}

class Post{

    public function require($params){

        if(!isset($_POST[$params])){
            return null;
        }else{
            if($_POST[$params] != null || $_POST[$params] != ''){
                return $_POST[$params];
            }
        }
    }
}

$post = new Post;

class App{

    protected $controller = 'home';
    protected $method = 'index';
    protected $params = [];

    public function __construct(){

        global $post;

        $url = $this->parseUrl();

        ($url == null)?$url[0] = $this->controller:'';

        // classes
        if(file_exists('app/controllers/'.$url[0].'.php')){
            $this->controller = $url[0];
            unset($url[0]);
        }

        require_once 'app/controllers/'.$this->controller.'.php';

        $this->controller = new $this->controller;

        // method or function of classes
        if(isset($url[1])){
            if(method_exists($this->controller, $url[1])){
                $this->method = $url[1];
                unset($url[1]);
            }else{
                $this->method = 'error_404';
            }
        }

        if(isset($url[0])){
            if(method_exists($this->controller, $url[0])){
                $this->method = $url[0];
                unset($url[0]);
            }else{
                $this->method = 'error_404';
            }
        }

        $this->params = $url ? array_values($url) : [];

        call_user_func_array([$this->controller, $this->method], [$post,$this->params]);
    }

    public function parseUrl(){
        if(isset($_GET['url'])){
            return $url = explode('/', filter_var(rtrim($_GET['url'],'/'), FILTER_SANITIZE_URL));
        }
    }
}