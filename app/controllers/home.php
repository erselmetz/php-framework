<?php

class Home extends Controller{

    public function index(){
        $this->view('home');
    }

    public function test($post,$er,){
        header('Content-Type: application/json');
        $array = [
            'test' => $post->require('test'),
            'test1' => $post->require('test1'),
            'test2' => $post->require('test2')
        ];
        
        echo json_encode($array);
    }
}