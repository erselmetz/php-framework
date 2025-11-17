<?php

namespace Connection;

use mysqli;
use PDO;
use Core\Logger;

/**
 * Database Query Builder
 * 
 * Provides a fluent interface for building and executing database queries.
 * Uses static properties for query building - reset after each query execution.
 */
class Database
{
    /** @var string Database host */
    private static $host;
    
    /** @var string Database username */
    private static $username;
    
    /** @var string Database password */
    private static $password;
    
    /** @var string Database name */
    private static $dbname;

    // Query builder properties
    /** @var string|null Raw SQL query */
    public static $query = null;
    
    /** @var string|null Table name */
    public static $table = null;
    
    /** @var string SELECT columns */
    public static $select = "*";
    
    /** @var string WHERE clause */
    public static $where = '';
    
    /** @var string Additional WHERE conditions */
    public static $andWhere = '';
    
    /** @var string ORDER BY clause */
    public static $orderBy = '';
    
    /** @var string|int LIMIT clause */
    public static $limit = '';

    /**
     * Initialize database connection settings
     */
    public function __construct()
    {
        global $database;
        
        if (!isset($database)) {
            Logger::error("Database configuration not found");
            throw new \Exception("Database configuration is missing");
        }
        
        Database::$host = $database['host'] ?? 'localhost';
        Database::$username = $database['username'] ?? 'root';
        Database::$password = $database['password'] ?? '';
        Database::$dbname = $database['dbname'] ?? '';
    }

    /**
     * Set raw SQL query
     * 
     * @param string $params SQL query string
     * @return self For method chaining
     */
    public static function query(string $params): self
    {
        Database::$query = $params;
        return new Database;
    }

    /**
     * Set table name for query
     * 
     * @param string $params Table name
     * @return self For method chaining
     */
    public static function table(string $params): self
    {
        Database::$table = $params;
        return new Database;
    }

    /**
     * Set SELECT columns
     * 
     * @param string $params Column names (comma-separated or *)
     * @return self For method chaining
     */
    public static function select(string $params): self
    {
        Database::$select = $params;
        return new Database;
    }

    /**
     * Set WHERE clause
     * 
     * @param string $params WHERE condition
     * @return self For method chaining
     */
    public static function where(string $params): self
    {
        Database::$where = $params;
        Database::$andWhere = ''; // Reset additional conditions
        return new Database;
    }

    /**
     * Add additional WHERE condition with AND
     * 
     * @param string $params Additional WHERE condition
     * @return self For method chaining
     */
    public static function andWhere(string $params): self
    {
        if (Database::$where !== '') {
            Database::$andWhere .= " AND " . $params;
        }
        return new Database;
    }

    /**
     * Set ORDER BY clause
     * 
     * @param string|null $params ORDER BY clause
     * @return self For method chaining
     */
    public static function orderBy(?string $params): self
    {
        if ($params !== null) {
            Database::$orderBy = $params;
        }
        return new Database;
    }

    /**
     * Set LIMIT clause
     * 
     * @param int|null $params Limit number
     * @return self For method chaining
     */
    public static function limit(?int $params): self
    {
        if ($params !== null) {
            Database::$limit = max(0, (int) $params);
        }
        return new Database;
    }

    /**
     * Execute query and return results
     * 
     * @return array Array of associative arrays (rows)
     * @throws \Exception If database connection fails or query error
     */
    public static function get(): array
    {
        $db = Database::connect();
        $results = [];
        
        // Check connection
        if ($db->connect_errno) {
            $error = "Failed to connect to MySQL: " . $db->connect_error;
            Logger::error($error, [
                'host' => Database::$host,
                'database' => Database::$dbname
            ]);
            throw new \Exception($error);
        }

        $result = false;

        // Execute raw query if provided
        if (Database::$query !== null) {
            $result = $db->query(Database::$query);
            if (!$result) {
                Logger::error("Query execution failed", [
                    'query' => Database::$query,
                    'error' => $db->error
                ]);
            }
        }
        // Build and execute query from builder
        elseif (Database::$table !== null) {
            $sql = Database::buildQuery();
            $result = $db->query($sql);
            
            if (!$result) {
                Logger::error("Query execution failed", [
                    'sql' => $sql,
                    'error' => $db->error
                ]);
            }
        }

        // Fetch results
        if ($result && $result !== false) {
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
            $result->free();
        }

        // Reset builder for next query
        Database::resetBuilder();
        
        return $results;
    }

    /**
     * Build SQL query from builder properties
     * 
     * @return string Complete SQL query
     */
    private static function buildQuery(): string
    {
        $sql = "SELECT " . Database::$select;
        $sql .= ' FROM ' . Database::$table;
        
        if (Database::$where !== '') {
            $sql .= ' WHERE ' . Database::$where;
        }
        
        if (Database::$andWhere !== '') {
            $sql .= Database::$andWhere;
        }
        
        if (Database::$orderBy !== '') {
            $sql .= ' ORDER BY ' . Database::$orderBy;
        }
        
        if (Database::$limit !== '') {
            $sql .= ' LIMIT ' . Database::$limit;
        }
        
        return $sql;
    }

    /**
     * Create database connection
     * 
     * @return mysqli Database connection instance
     * @throws \Exception If connection fails
     */
    private static function connect(): mysqli
    {
        $connection = @new mysqli(
            Database::$host,
            Database::$username,
            Database::$password,
            Database::$dbname
        );
        
        if ($connection->connect_error) {
            Logger::error("Database connection failed", [
                'error' => $connection->connect_error,
                'host' => Database::$host
            ]);
            throw new \Exception("Database connection failed: " . $connection->connect_error);
        }
        
        return $connection;
    }

    /**
     * Reset query builder properties
     * 
     * @return void
     */
    private static function resetBuilder(): void
    {
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