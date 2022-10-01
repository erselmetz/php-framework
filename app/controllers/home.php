<?php

class Home extends Controller{

    public function index($name = ''){

        $this->view('home',['name'=>$name]);

    }
}