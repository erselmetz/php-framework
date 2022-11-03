<?php

class Home extends Controller{

    public function index($get){
        $this->view('home');
    }

    // POST METHOD
    public function test(){
        header('Content-Type: application/json');
        $array = [
            'test' => Post::require('test'),
            'test1' => Post::require('test1'),
            'test2' => Post::require('test2')
        ];
        
        echo json_encode($array);
    }

    // GET METHOD
    public function test1($get){
        echo $get[0];
        echo $get[1];
        echo $get[2];
    }

    // DB test
    public function dbtest($get){

        $post_content = User::post_content();

        $this->view('home',[
            'post'=>$post_content
        ]);
    }
}