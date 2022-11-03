<?php

use Connection\Database as DB;

class User extends DB{

    public static function post_content(){

        $results = DB::table("wp_posts")
            ->where("post_author = 1")
            ->andWhere("comment_status = 'closed'")
            ->andWhere("post_status = 'publish'")
            ->andWhere("post_title != ''")
            ->orderBy('ID desc')
            ->limit(10)
            ->get();

        return $results;
    }
}