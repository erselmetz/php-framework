<?php

namespace Connection;

use mysqli;
use PDO;

class Database{

    private static $host;
    private static $username;
    private static $password;
    private static $dbname;

    public static $query = false;
    public static $table = false;
    public static $select = "*";
    public static $where = false;
    public static $andWhere = false;
    public static $orderBy = false;
    public static $limit = false;

    public function __construct(){
        global $database;
        Database::$host = $database['host'];
        Database::$username = $database['username'];
        Database::$password = $database['password'];
        Database::$dbname = $database['dbname'];
    }

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

class SQLite{

    private static $SQLite_file;

    public static $query = false;
    public static $table = false;
    public static $select = "*";
    public static $where = false;
    public static $andWhere = false;
    public static $orderBy = false;
    public static $limit = false;

    public function __construct(){
        global $SQLite;
        SQLite::$SQLite_file = $SQLite;
    }

    public static function query($params){
        SQLite::$query = $params;
        return new SQLite;
    }

    public static function table($params){
        SQLite::$table = $params;
        return new SQLite;
    }

    public static function select($params){
        SQLite::$select = $params;
        return new SQLite;
    }

    public static function where($params){
        SQLite::$where = " WHERE ".$params;
        return new SQLite;
    }

    public static function andWhere($params){
        if(SQLite::$where != false){
            SQLite::$andWhere .= " AND ".$params;
        }
        return new SQLite;
    }

    public static function orderBy($params){
        if($params != null){
            SQLite::$orderBy = " ORDER BY ".$params;
        }
        return new SQLite;
    }

    public static function limit($params){
        if($params != null){
            SQLite::$limit = " LIMIT ".$params;
        }
        return new SQLite;
    }

    public static function get(){
        $db = SQLite::connect();

        // if query is in used
        if(SQLite::$query != false){
            $result = $db->query(SQLite::$query);
            SQLite::$table = false;
        }

        // if table is in used
        if(SQLite::$table != false){
            $sql = "SELECT ";
            $sql .= SQLite::$select;
            $sql .= ' FROM '.SQLite::$table;
            $sql .= SQLite::$where;
            $sql .= SQLite::$andWhere;
            $sql .= SQLite::$orderBy;
            $sql .= SQLite::$limit;
            
            // echo $sql;
            $result = $db->query($sql);
            SQLite::$query = false;
        }

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function connect(){
        return new PDO("sqlite:".SQLite::$SQLite_file);
    }
}