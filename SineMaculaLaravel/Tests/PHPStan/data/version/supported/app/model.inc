<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thing extends Model
{
    protected $table = 'things';

    protected $hidden = ['secret'];
}
