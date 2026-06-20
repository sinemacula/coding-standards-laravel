<?php

namespace App\Models;

class User
{
    public function getFullNameAttribute(): string
    {
        return 'x';
    }

    public function setEmailAttribute(string $value): void
    {
        //
    }

    public function getAttribute(string $key): mixed
    {
        return null;
    }

    public function name(): string
    {
        return 'x';
    }
}
