<?php

class Home extends Controller{

    public function index($name = ''){

        $user = User::Select('fname')->get();
        
        $this->view('home',$user);

    }
}