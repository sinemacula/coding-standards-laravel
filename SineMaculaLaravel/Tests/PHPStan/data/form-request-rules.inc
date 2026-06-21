<?php

namespace App\Http\Requests;

class CreateUserRequest
{
    public function rules(): array
    {
        return [];
    }
}

class UpdateUserRequest
{
    public function authorize(): bool
    {
        return true;
    }
}

abstract class BaseRequest
{
}
