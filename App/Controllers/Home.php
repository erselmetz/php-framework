<?php

class Home extends Controller{

    public function index($post, $get){
        $this->view('home');
    }

    // POST METHOD
    public function test($post, $get){
        header('Content-Type: application/json');
        $array = [
            'test' => $post->require('test'),
            'test1' => $post->require('test1'),
            'test2' => $post->require('test2')
        ];
        
        echo json_encode($array);
    }

    // GET METHOD
    public function test1($post, $get){
        echo $get[0];
        echo $get[1];
        echo $get[2];
    }

    // DB test
    public function dbtest($post, $get){

        $user = User::get_info();

        $this->view('home',$user);
    }
}