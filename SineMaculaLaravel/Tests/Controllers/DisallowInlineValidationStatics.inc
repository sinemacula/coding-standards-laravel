<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;

class SettingsController
{
    public function update(): void
    {
        Validator::extend('custom', 'CustomValidator@handle');
        Validator::make([], []);
    }
}
