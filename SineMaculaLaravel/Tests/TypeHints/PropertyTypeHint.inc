<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';

    protected $fillable = ['name'];

    protected $dateFormat = 'Y-m-d';

    public $nickname;

    public int $age = 0;

    public function describe($value): string
    {
        $label = (string) $value;

        return $label;
    }
}

$topLevel = 1;
