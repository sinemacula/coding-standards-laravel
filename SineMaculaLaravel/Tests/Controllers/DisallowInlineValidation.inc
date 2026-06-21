<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;

class UserController
{
    public function store($request, $factory): void
    {
        $request->validate(['name' => 'required']);
        $this->validate($request, []);
        Validator::make([], []);
        $factory->make('thing');
        Other::make('thing');
        validate($request);
    }
}

class UserService
{
    public function handle($request): void
    {
        $request->validate(['name' => 'required']);
    }
}
