<?php

use Connection\Database as DB;

class User{

    public static function post_content(){

        $results = DB::query("SELECT * FROM wp_posts")->get();

        return $results;
    }
}