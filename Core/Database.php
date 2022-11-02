<?php

namespace Connection;

use mysqli;

class Database{

    private static $host = 'localhost';
    private static $username = 'root';
    private static $password = '';
    private static $dbname = 'test';

    public static $table = '';
    public static $query = '';

    public static function query($params){
        Database::$query = $params;
        return new Database;
    }

    public static function table($params){
        Database::$table = $params;
        return new Database;
    }

    public static function get(){
        $db = Database::connect();
        
        if ($db -> connect_errno) {
            echo "Failed to connect to MySQL: " . $db -> connect_error;
            exit();
        }

        $result = $db->query(Database::$query);

        $array = [];

        while($row = $result->fetch_assoc()){
            array_push($array,$row);
        }

        return $array;
    }

    private static function connect(){

        return new mysqli(Database::$host, Database::$username, Database::$password, Database::$dbname);
    }
}