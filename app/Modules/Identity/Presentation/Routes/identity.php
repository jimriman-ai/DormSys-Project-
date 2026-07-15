<?php

declare(strict_types=1);

use App\Modules\Identity\Presentation\Http\Controllers\RoleController;
use App\Modules\Identity\Presentation\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::get('roles', [RoleController::class, 'index'])->name('identity.roles.index');
Route::post('roles', [RoleController::class, 'store'])->name('identity.roles.store');
Route::patch('roles/{role}', [RoleController::class, 'update'])->name('identity.roles.update');
Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('identity.roles.destroy');
Route::get('roles/{role}/users', [UserRoleController::class, 'users'])->name('identity.roles.users');
Route::put('users/{user}/roles', [UserRoleController::class, 'sync'])->name('identity.users.roles.sync');
