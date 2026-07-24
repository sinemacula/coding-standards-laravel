<?php

Route::prefix('a')->group(function () {
    Route::get('roles', [RoleController::class, 'index']);
});

Route::prefix('b')->group(function () {
    Route::post('roles', [RoleController::class, 'store']);
});
