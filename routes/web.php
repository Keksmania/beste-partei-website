<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\UserController;

// Standard pages
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

// Login

//Route::middleware('throttle:5|5,1')->group(function () {
   // Route::post('/register', [UserController::class, 'register']);

//});

Route::get('/login', function () {
    return view('login'); 
})->name('login');

// Post content
Route::get('/post/{id}', [ContentController::class, 'getPost']);


//Route::get('/register', function () {
 //   return view('register'); 
//});




// Group routes for API with a prefix
Route::prefix('api')->group(function () {
    Route::get('/events', [ContentController::class, 'index']);
    Route::middleware('throttle:10|10,1')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
    });

});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/createpost', function () {
        return view('createpost'); 
    });
    Route::prefix('api')->group(function () {
       Route::post('/events', [ContentController::class, 'store']);
      }  );
});