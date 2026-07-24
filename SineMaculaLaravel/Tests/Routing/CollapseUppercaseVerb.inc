<?php

Route::GET('roles', [RoleController::class, 'index']);
Route::POST('roles', [RoleController::class, 'store']);
