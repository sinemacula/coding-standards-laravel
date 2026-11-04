<?php

namespace App\Billing\Validation;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChargeRequest extends FormRequest
{
}

class SomeService
{
}

class MinimumAge implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
    }
}
