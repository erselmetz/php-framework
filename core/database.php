<?php

namespace Connection;

use mysqli;
use PDO;

class Database{

    private static $host;
    private static $username;
    private static $password;
    private static $dbname;

    public static $query = null;
    public static $table = null;
    public static $select = "*";
    public static $where = '';
    public static $andWhere = '';
    public static $orderBy = '';
    public static $limit = '';

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
        Database::$where = $params;
        Database::$andWhere = '';
        return new Database;
    }

    public static function andWhere($params){
        if(Database::$where !== ''){
            Database::$andWhere .= " AND ".$params;
        }
        return new Database;
    }

    public static function orderBy($params){
        if($params != null){
            Database::$orderBy = $params;
        }
        return new Database;
    }

    public static function limit($params){
        if($params != null){
            Database::$limit = (int) $params;
        }
        return new Database;
    }

    public static function get(){
        $db = Database::connect();
        $array = [];
        $result = false;
        
        if ($db -> connect_errno) {
            echo "Failed to connect to MySQL: " . $db -> connect_error;
            exit();
        }

        // if query is in used
        if(Database::$query !== null){
            $result = $db->query(Database::$query);
        }

        // if table is in used
        if(Database::$table !== null){
            $sql = "SELECT ";
            $sql .= Database::$select;
            $sql .= ' FROM '.Database::$table;
            if(Database::$where !== ''){
                $sql .= ' WHERE '.Database::$where;
            }
            if(Database::$andWhere !== ''){
                $sql .= Database::$andWhere;
            }
            if(Database::$orderBy !== ''){
                $sql .= ' ORDER BY '.Database::$orderBy;
            }
            if(Database::$limit !== ''){
                $sql .= ' LIMIT '.Database::$limit;
            }
            
            // echo $sql;
            $result = $db->query($sql);
        }

        if($result){
            while($row = $result->fetch_assoc()){
                array_push($array,$row);
            }
        }

        Database::resetBuilder();
        return $array;
    }

    private static function connect(){

        return new mysqli(Database::$host, Database::$username, Database::$password, Database::$dbname);
    }

    private static function resetBuilder(){
        Database::$query = null;
        Database::$table = null;
        Database::$select = "*";
        Database::$where = '';
        Database::$andWhere = '';
        Database::$orderBy = '';
        Database::$limit = '';
    }
}

class SQLite{

    private static $SQLite_file;

    public static $query = null;
    public static $table = null;
    public static $select = "*";
    public static $where = '';
    public static $andWhere = '';
    public static $orderBy = '';
    public static $limit = '';

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
        SQLite::$where = $params;
        SQLite::$andWhere = '';
        return new SQLite;
    }

    public static function andWhere($params){
        if(SQLite::$where !== ''){
            SQLite::$andWhere .= " AND ".$params;
        }
        return new SQLite;
    }

    public static function orderBy($params){
        if($params != null){
            SQLite::$orderBy = $params;
        }
        return new SQLite;
    }

    public static function limit($params){
        if($params != null){
            SQLite::$limit = (int) $params;
        }
        return new SQLite;
    }

    public static function get(){
        $db = SQLite::connect();
        $result = false;

        // if query is in used
        if(SQLite::$query !== null){
            $result = $db->query(SQLite::$query);
        }

        // if table is in used
        if(SQLite::$table !== null){
            $sql = "SELECT ";
            $sql .= SQLite::$select;
            $sql .= ' FROM '.SQLite::$table;
            if(SQLite::$where !== ''){
                $sql .= ' WHERE '.SQLite::$where;
            }
            if(SQLite::$andWhere !== ''){
                $sql .= SQLite::$andWhere;
            }
            if(SQLite::$orderBy !== ''){
                $sql .= ' ORDER BY '.SQLite::$orderBy;
            }
            if(SQLite::$limit !== ''){
                $sql .= ' LIMIT '.SQLite::$limit;
            }
            
            // echo $sql;
            $result = $db->query($sql);
        }

        $data = $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];

        SQLite::resetBuilder();
        return $data;
    }
    
    public static function connect(){
        return new PDO("sqlite:".SQLite::$SQLite_file);
    }

    private static function resetBuilder(){
        SQLite::$query = null;
        SQLite::$table = null;
        SQLite::$select = "*";
        SQLite::$where = '';
        SQLite::$andWhere = '';
        SQLite::$orderBy = '';
        SQLite::$limit = '';
    }
}