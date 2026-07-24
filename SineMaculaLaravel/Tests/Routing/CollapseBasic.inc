<?php

Route::get('roles', [RoleController::class, 'index']);
Route::post('roles', [RoleController::class, 'store']);
Route::delete('roles/{role}', [RoleController::class, 'destroy'])
    ->whereUuid('role');
