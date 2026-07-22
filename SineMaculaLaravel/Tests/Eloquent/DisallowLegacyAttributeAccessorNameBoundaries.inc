<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    public function forgetNameAttribute(): void
    {
    }

    public function getNameAttributeRaw(): string
    {
        return '';
    }

    public function touchName(string $value): void
    {
    }

    public function getEmailAttribute(): string
    {
        return '';
    }
}
