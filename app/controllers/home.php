<?php

class Home extends Controller{

    public function index($name = ''){
        $user = User::Select();
        $this->view('home',['name'=>$user]);

    }
}