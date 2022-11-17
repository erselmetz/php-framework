<?php

class Html extends Controller{

    public function index($get){
        $this->view('home');
    }

    public function e404(){
        $this->view('error/404');
    }

    public function login(){
        echo "this is login page";
    }
}