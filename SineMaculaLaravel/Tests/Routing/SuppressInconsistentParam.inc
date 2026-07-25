<?php

Route::get('roles/{role}', [RoleController::class, 'show']);
Route::delete('roles/{roleId}', [RoleController::class, 'destroy']);
