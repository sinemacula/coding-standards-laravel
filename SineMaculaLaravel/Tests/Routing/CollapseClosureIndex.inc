<?php

Route::get('roles', fn () => 'listing');
Route::post('roles', [RoleController::class, 'store']);
Route::delete('roles/{role}', [RoleController::class, 'destroy']);
