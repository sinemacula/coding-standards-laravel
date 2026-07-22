<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public function getNameAttribute(): string
    {
        $format = function (): string {
            return '';
        };

        return $format();
    }

    public function setNameAttribute($value): void
    {
    }

    public function getProfileAttribute(string $key): string
    {
        return $key;
    }

    public function setTagsAttribute($value, $extra): void
    {
    }

    public function fullName(): string
    {
        return '';
    }

    public function applyDiscount(int $amount): int
    {
        return $amount;
    }
}

class ProfileService
{
    public function setFooAttribute(Model $model, string $key, mixed $value): void
    {
    }

    public function getBarAttribute(): string
    {
        return '';
    }
}

class Money extends ValueObject
{
    public function getCentsAttribute(): int
    {
        return 0;
    }
}

function getSystemAttribute(): string
{
    return '';
}
