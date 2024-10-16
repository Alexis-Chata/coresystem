<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\MarcaController;

Route::get('/user-roles', [UserRoleController::class, 'index']);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/users/roles', [UserRoleController::class, 'index'])->name('users.roles');
    Route::put('/users/{user}/assign-role', [UserRoleController::class, 'assignRole'])->name('users.assign-role');
});

Route::get('/user-roles', [UserRoleController::class, 'index'])->name('user-roles.index');
Route::put('/users/{user}/assign-role', [UserRoleController::class, 'assignRole'])->name('users.assign-role');

Route::get('/marcas', [MarcaController::class, 'index'])->name('marcas.index');
