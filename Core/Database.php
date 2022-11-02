<?php

namespace Connection;

use mysqli;

class Database{

    private static $host = 'localhost';
    private static $username = 'root';
    private static $password = '';
    private static $dbname = 'test';

    public static function query($params){
        
        $db = Database::connect();
        
        if ($db -> connect_errno) {
            echo "Failed to connect to MySQL: " . $db -> connect_error;
            exit();
        }

        return $db->query($params);
    }

    private static function connect(){

        return new mysqli(Database::$host, Database::$username, Database::$password, Database::$dbname);
    }
}