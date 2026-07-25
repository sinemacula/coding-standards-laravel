<?php

Route::middleware('auth')->group(function () {
    Route::get('roles', [RoleController::class, 'index']);
    Route::post('roles', [RoleController::class, 'store']);
});

Route::middleware('admin')->group(function () {
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->whereUuid('role');
});
