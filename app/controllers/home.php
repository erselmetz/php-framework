<?php

class Home extends Controller{

    public function index(){
        $this->view('home');
    }

    public function test($post){
        $test = $post->require('test');
        $test1 = $post->require('test1');
        $test2 = $post->require('test2');

        printf(json_encode(["test"=>$test, "test1"=>$test1, "test2"=>$test2]));
    }
}