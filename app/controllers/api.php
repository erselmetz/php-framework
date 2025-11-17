<?php

use Core\Upload;
use Core\Cache;
use Core\Logger;

/**
 * API Controller
 * 
 * Handles API endpoints for validation, file uploads, caching, and user data.
 */
class Api extends Controller
{
    /**
     * Validate and store data
     * 
     * @return void Sends JSON response
     */
    public function store(): void
    {
        try {
            $validator = $this->validate($_POST, [
                'name' => 'required|min:3|max:50',
                'email' => 'required|email',
                'age' => 'required|integer|min:18'
            ]);

            if ($validator->fails()) {
                Logger::warning("Validation failed in API store", [
                    'errors' => $validator->errors()
                ]);
                $this->error('Validation failed', 422, $validator->errors());
                return;
            }

            $data = $validator->validated();
            $this->success($data, 'Data validated successfully');
            
        } catch (\Exception $e) {
            Logger::error("Error in API store method", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->error('An error occurred while processing your request', 500);
        }
    }

    /**
     * Handle file upload
     * 
     * @return void Sends JSON response
     */
    public function upload(): void
    {
        try {
            if (!isset($_FILES['file'])) {
                $this->error('No file uploaded', 400);
                return;
            }

            $file = Upload::make('file')
                ->allowed(['jpg', 'jpeg', 'png', 'pdf'])
                ->maxSize(5242880) // 5MB
                ->path('storage/uploads')
                ->store();

            Logger::info("File uploaded successfully", ['filename' => $file]);
            $this->success(['filename' => $file], 'File uploaded successfully');
            
        } catch (\Exception $e) {
            Logger::error("File upload failed", [
                'error' => $e->getMessage()
            ]);
            $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Demonstrate caching functionality
     * 
     * @return void Sends JSON response
     */
    public function cached(): void
    {
        try {
            $data = Cache::remember('expensive_operation', function() {
                // Simulate expensive operation
                sleep(1);
                return ['result' => 'This was cached!', 'time' => time()];
            }, 3600); // Cache for 1 hour

            $this->json($data);
            
        } catch (\Exception $e) {
            Logger::error("Error in cached endpoint", [
                'error' => $e->getMessage()
            ]);
            $this->error('An error occurred', 500);
        }
    }

    /**
     * Get all users (uses User model)
     * 
     * @return void Sends JSON response
     */
    public function users(): void
    {
        try {
            $users = User::all();
            
            if (empty($users)) {
                // Fallback to sample data if database is empty or not available
                Logger::info("No users found in database, returning sample data");
                $users = [
                    ['id' => 1, 'name' => 'John'],
                    ['id' => 2, 'name' => 'Jane']
                ];
                $this->success($users, 'Users retrieved (sample data)');
                return;
            }
            
            $this->success($users, 'Users retrieved successfully');
            
        } catch (\Exception $e) {
            Logger::error("Error retrieving users", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback to sample data on error
            $users = [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane']
            ];
            $this->success($users, 'Users retrieved (sample data - database unavailable)');
        }
    }
}

