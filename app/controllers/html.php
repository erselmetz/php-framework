<?php

class Html extends Controller{

    protected $layout = 'main';

    public function index($get){
        $this->view('home', [
            'headline' => 'Welcome this is a Simple PHP Framework',
        ]);
    }

    public function e404(){
        $this->view('error/404');
    }

    public function login(){
        $this->view('auth/login', [
            'layout' => 'main',
        ]);
    }
}