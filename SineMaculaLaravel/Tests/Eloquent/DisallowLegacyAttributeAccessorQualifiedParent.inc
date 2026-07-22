<?php

namespace App\Models;

class Invoice extends \Illuminate\Database\Eloquent\Model
{
    public function getTotalAttribute(): int
    {
        return 0;
    }
}
