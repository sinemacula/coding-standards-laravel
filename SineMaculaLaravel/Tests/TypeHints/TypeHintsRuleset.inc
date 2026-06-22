<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Thing extends Model
{
    protected $table = 'things';

    public $untyped;

    #[\Override]
    public function handle($payload)
    {
    }
}
