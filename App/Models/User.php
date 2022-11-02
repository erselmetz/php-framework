<?php

use Connection\Database as DB;

class User extends DB{

    public static function get_info(){

        $result = DB::query("SELECT * FROM wp_posts");

        $array = [];

        while($row = $result->fetch_assoc()){
            array_push($array,$row);

        }

        return $array;
    }
}