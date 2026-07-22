<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountPresenter extends Presenter
{
    public function register(): void
    {
        class NestedAccount extends Model
        {
            public function getLabelAttribute(): string
            {
                return '';
            }
        }
    }
}
