<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;


Route::get('/', function () {
    return view('start');
});

Route::get('/Impressum', function () {
    return view('impressum'); 
});

Route::get('/Geschichte', function () {
    return view('geschichte'); 
});

Route::get('/Termine', function () {
    return view('termine'); 
});

Route::get('/Kontakt', function () {
    return view('kontakt'); 
});

Route::get('/Partei-Programm', function () {
    return view('partei-programm'); 
});



Route::get('/Login', function () {
    return view('login'); 
});

Route::post('/Login', [UserController::class, 'Login']);

// Group routes for API with a prefix
Route::prefix('api')->group(function () {
    Route::get('/events', [EventController::class, 'index']);
});