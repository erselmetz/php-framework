<?php

namespace Connection;

use mysqli;

class Database{

    private static $host = 'localhost';
    private static $username = 'root';
    private static $password = '';
    private static $dbname = 'test';

    public static $query = false;
    public static $table = false;
    public static $select = "*";
    public static $where = false;
    public static $andWhere = false;
    public static $orderBy = false;
    public static $limit = false;

    public static function query($params){
        Database::$query = $params;
        return new Database;
    }

    public static function table($params){
        Database::$table = $params;
        return new Database;
    }

    public static function select($params){
        Database::$select = $params;
        return new Database;
    }

    public static function where($params){
        Database::$where = " WHERE ".$params;
        return new Database;
    }

    public static function andWhere($params){
        if(Database::$where != false){
            Database::$andWhere .= " AND ".$params;
        }
        return new Database;
    }

    public static function orderBy($params){
        if($params != null){
            Database::$orderBy = " ORDER BY ".$params;
        }
        return new Database;
    }

    public static function limit($params){
        if($params != null){
            Database::$limit = " LIMIT ".$params;
        }
        return new Database;
    }

    public static function get(){
        $db = Database::connect();
        $array = [];
        
        if ($db -> connect_errno) {
            echo "Failed to connect to MySQL: " . $db -> connect_error;
            exit();
        }

        // if query is in used
        if(Database::$query != false){
            $result = $db->query(Database::$query);
            Database::$table = false;
        }

        // if table is in used
        if(Database::$table != false){
            $sql = "SELECT ";
            $sql .= Database::$select;
            $sql .= ' FROM '.Database::$table;
            $sql .= Database::$where;
            $sql .= Database::$andWhere;
            $sql .= Database::$orderBy;
            $sql .= Database::$limit;
            
            // echo $sql;
            $result = $db->query($sql);
            Database::$query = false;
        }

        while($row = $result->fetch_assoc()){
            array_push($array,$row);
        }
        return $array;
    }

    private static function connect(){

        return new mysqli(Database::$host, Database::$username, Database::$password, Database::$dbname);
    }
}