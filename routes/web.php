<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('start');
});

Route::get('/Impressum', function () {
    return view('impressum'); 
});

//Route::get('/Login', function () {
//    return view('login'); 
//});

//Route::post('/Login', [UserController::class, 'Login']);