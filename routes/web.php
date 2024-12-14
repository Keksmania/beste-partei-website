<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use App\Mail\TestMail;
use Illuminate\Support\Facades\Mail;

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
Route::get('/login', function () {
    return view('login');
})->name('login');


Route::get('/register', function () {
    return view('register');
});

Route::get('/registration-list', function () {
    $user = Auth::user();
        if ($user && $user->hasPermission('admin')) {
            return view('approve-registration');
        }
        abort(403, 'Unauthorized');
   
});


// Post content
Route::get('/post/{id}', [ContentController::class, 'getPost']);

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Manage permissions route
    Route::get('/manage-permissions', function () {
        $user = Auth::user();
        if ($user && $user->hasPermission('admin')) {
            return view('manage-permissions');
        }
        abort(403, 'Unauthorized');
    });

    // Create post route
    Route::get('/createpost', function () {
        $user = Auth::user();
        if ($user && $user->hasPermission('posten')) {
            return view('createpost');
        }
        abort(403, 'Unauthorized');
    });

    // API routes for managing permissions and users
    Route::prefix('api')->group(function () {
        Route::get('/users', function (Request $request) {
            $user = Auth::user();
            if ($user && $user->hasPermission('admin')) {
                return (new PermissionController)->getUsersWithPermissions($request);
            }
            abort(403, 'Unauthorized');
        });

        Route::get('/permissions', function (Request $request) {
            $user = Auth::user();
            if ($user && $user->hasPermission('admin')) {
                return (new PermissionController)->getPermissions($request);
            }
            abort(403, 'Unauthorized');
        });

        Route::post('/assign-permission', function (Request $request) {
            $user = Auth::user();
            if ($user && $user->hasPermission('admin')) {
                return (new PermissionController)->assignPermission($request);
            }
            abort(403, 'Unauthorized');
        });

        Route::post('/revoke-permission', function (Request $request) {
            $user = Auth::user();
            if ($user && $user->hasPermission('admin')) {
                return (new PermissionController)->revokePermission($request);
            }
            abort(403, 'Unauthorized');
        });

        // New routes for registration approval system
        Route::get('/registrations', function (Request $request) {
            $user = Auth::user();
            if ($user && $user->hasPermission('admin')) {
                return (new UserController)->getRegistrations($request);
            }
            abort(403, 'Unauthorized');
        });

        Route::post('/approve-registration/{id}', function ($id) {
            $user = Auth::user();
            if ($user && $user->hasPermission('admin')) {
                return (new UserController)->approveRegistration($id);
            }
            abort(403, 'Unauthorized');
        });
    });

    // API route for storing events
    Route::prefix('api')->group(function () {
        Route::post('/events', function (Request $request) {
            $user = Auth::user();
            if ($user && $user->hasPermission('posten')) {
                return (new ContentController)->store($request);
            }
            abort(403, 'Unauthorized');
        });
    });
});

// Group routes for API with a prefix
Route::prefix('api')->group(function () {
    Route::get('/events', [ContentController::class, 'index']);
    Route::middleware('throttle:10|10,1')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);       
        Route::post('/register', [UserController::class, 'register']);
    });
});


// Logout route
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::get('/verify-email', [App\Http\Controllers\AuthController::class, 'verifyEmail']);


