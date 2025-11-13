<?php

use Core\Upload;
use Core\Cache;

class Api extends Controller
{
    // Example: Validation
    public function store()
    {
        $validator = $this->validate($_POST, [
            'name' => 'required|min:3|max:50',
            'email' => 'required|email',
            'age' => 'required|integer|min:18'
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors());
        }

        $data = $validator->validated();
        return $this->success($data, 'Data validated successfully');
    }

    // Example: File Upload
    public function upload()
    {
        try {
            $file = Upload::make('file')
                ->allowed(['jpg', 'jpeg', 'png', 'pdf'])
                ->maxSize(5242880) // 5MB
                ->path('storage/uploads')
                ->store();

            return $this->success(['filename' => $file], 'File uploaded successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    // Example: Caching
    public function cached()
    {
        $data = Cache::remember('expensive_operation', function() {
            // Simulate expensive operation
            sleep(1);
            return ['result' => 'This was cached!', 'time' => time()];
        }, 3600); // Cache for 1 hour

        return $this->json($data);
    }

    // Example: JSON Response
    public function users()
    {
        $users = [
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane']
        ];

        return $this->success($users, 'Users retrieved');
    }
}

