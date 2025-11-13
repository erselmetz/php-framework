<?php

class Form extends Controller
{
    protected $middleware = [
        ['name' => 'csrf', 'only' => ['submit']]
    ];

    public function show()
    {
        $this->view('form/show', [
            'csrf_token' => $this->csrfToken()
        ]);
    }

    public function submit()
    {
        $validator = $this->validate($_POST, [
            'name' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            flash('errors', $validator->errors());
            return $this->back();
        }

        flash('success', 'Form submitted successfully!');
        return $this->redirect(route('home') ?? '/');
    }
}

