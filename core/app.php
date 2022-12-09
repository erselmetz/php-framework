<?php

function assets($params){

    global $RewriteBase;
    return $RewriteBase.'public/'.$params;

}

class Post{

    private static $var;
    private static $limit;

    public static function require($params){

        if(isset($_POST[$params])){

            if($_POST[$params] != null || $_POST[$params] != ''){

                $a = htmlspecialchars($_POST[$params]);
                $b = htmlentities($a);
                $c = strip_tags($b);
                Post::$var = $c;

            }
        }
        return new Post;

    }

    public static function limit($params){

        if(is_numeric($params)){

            Post::$limit = $params;

        }
    }

    public static function get(){

        if(strlen(Post::$var >= Post::$limit)){

            Post::$var = "value is limited only to ".Post::$limit;

        }

        return Post::$var;
    }
}

class Auth{

    public static function user(){
        
        $response = false;

        if(isset($_SESSION['email']) && isset($_SESSION['password'])){

            $response = true;

        }

        return $response;
    }
}

class App{

    protected $controller = 'html';
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

        // method/function of classes
        if(isset($url[0])){

            if(method_exists($this->controller, $url[0])){

                $this->method = $url[0];
                unset($url[0]);

            }
        }
        
        if(isset($url[1])){

            if(method_exists($this->controller, $url[1])){

                $this->method = $url[1];
                unset($url[1]);

            }
        }

        $this->params = $url ? array_values($url) : [];

        call_user_func_array([$this->controller, $this->method], [$this->params]);

    }

    public function parseUrl(){

        if(isset($_GET['url'])){

            return $url = explode('/', filter_var(rtrim($_GET['url'],'/'), FILTER_SANITIZE_URL));

        }
    }
}