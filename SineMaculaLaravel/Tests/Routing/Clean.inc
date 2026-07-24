<?php

Route::apiResource('roles', RoleController::class);
Route::resource('photos', PhotoController::class);
Route::get('ping', fn () => 'pong');
Route::get('status', [StatusController::class, 'show']);
