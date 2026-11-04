<?php

namespace App\Http\Requests\Backgrounds\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MinimumAge implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
    }
}

class RequiresStreetNumber
{
    public function passes(): bool
    {
        return true;
    }
}
