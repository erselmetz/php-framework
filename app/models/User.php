<?php

use Connection\Database as DB;
use Core\Logger;

/**
 * User Model
 * 
 * Provides methods for interacting with the users table.
 */
class User extends DB
{
    /**
     * Get all users
     * 
     * @return array Array of user records
     */
    public static function all(): array
    {
        try {
            return \Connection\Database::table('users')->get();
        } catch (\Exception $e) {
            Logger::error("Failed to fetch all users", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Find user by ID
     * 
     * @param int|string $id User ID
     * @return array|null User record or null if not found
     */
    public static function find($id): ?array
    {
        try {
            $id = (int) $id;
            if ($id <= 0) {
                return null;
            }
            
            $users = \Connection\Database::table('users')->where("id = $id")->get();
            return !empty($users) ? $users[0] : null;
        } catch (\Exception $e) {
            Logger::error("Failed to find user by ID", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Find user by email address
     * 
     * @param string $email User email address
     * @return array|null User record or null if not found
     */
    public static function findByEmail(string $email): ?array
    {
        try {
            global $database;
            
            // Create connection for escaping
            $db = new mysqli(
                $database['host'] ?? 'localhost',
                $database['username'] ?? 'root',
                $database['password'] ?? '',
                $database['dbname'] ?? ''
            );
            
            if ($db->connect_error) {
                Logger::error("Database connection failed in findByEmail", [
                    'error' => $db->connect_error
                ]);
                return null;
            }
            
            // Escape email to prevent SQL injection
            $email = $db->real_escape_string($email);
            $db->close();
            
            // Query using query builder
            $users = \Connection\Database::table('users')->where("email = '$email'")->get();
            return !empty($users) ? $users[0] : null;
        } catch (\Exception $e) {
            Logger::error("Failed to find user by email", [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create a new user
     * 
     * @param array $data User data (name, email, password)
     * @return array|null Created user record or null on failure
     */
    public static function create(array $data): ?array
    {
        try {
            global $database;
            
            // Validate required fields
            if (empty($data['email'])) {
                Logger::warning("Attempted to create user without email");
                return null;
            }
            
            $db = new mysqli(
                $database['host'] ?? 'localhost',
                $database['username'] ?? 'root',
                $database['password'] ?? '',
                $database['dbname'] ?? ''
            );
            
            if ($db->connect_error) {
                Logger::error("Database connection failed in create", [
                    'error' => $db->connect_error
                ]);
                return null;
            }
            
            // Escape and prepare data
            $name = $db->real_escape_string($data['name'] ?? '');
            $email = $db->real_escape_string($data['email'] ?? '');
            $password = isset($data['password']) 
                ? password_hash($data['password'], PASSWORD_DEFAULT) 
                : '';
            $password = $db->real_escape_string($password);
            
            // Build and execute insert query
            $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
            
            if ($db->query($sql)) {
                $userId = $db->insert_id;
                $db->close();
                
                // Fetch and return created user
                $users = \Connection\Database::table('users')->where("id = $userId")->get();
                return !empty($users) ? $users[0] : null;
            }
            
            Logger::error("Failed to create user", [
                'error' => $db->error,
                'email' => $email
            ]);
            $db->close();
            return null;
            
        } catch (\Exception $e) {
            Logger::error("Exception while creating user", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}

