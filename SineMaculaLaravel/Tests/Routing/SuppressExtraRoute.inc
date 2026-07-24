<?php

Route::get('roles', [RoleController::class, 'index']);
Route::post('roles', [RoleController::class, 'store']);
Route::post('roles/{role}/restore', [RoleController::class, 'restore']);
