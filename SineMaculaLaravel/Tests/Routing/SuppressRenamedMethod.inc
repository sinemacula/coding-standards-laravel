<?php

Route::get('roles', [RoleController::class, 'list']);
Route::post('roles', [RoleController::class, 'store']);
