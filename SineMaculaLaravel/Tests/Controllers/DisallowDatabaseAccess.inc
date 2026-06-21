<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController
{
    public function index($model): void
    {
        DB::table('users')->get();
        User::find(1);
        User::where('active', true)->get();
        Carbon::now();
        $model::find(1);
        $this->repository->find(1);
        $timestamp = User::CREATED_AT;
    }
}

class ReportService
{
    public function build(): void
    {
        DB::table('users')->get();
    }
}
