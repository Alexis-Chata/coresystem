<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::post('git_pull', function(){
//     return Http::get('http://128.140.10.123:3000/api/box/deploy/4c6ee4554ce1435bf1d457abb315637efcf43314be732d0d');
// });
