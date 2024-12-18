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


Route::middleware(['guest'])->group(function () {
    Route::get('/register', function () {
        return view('register');
    })->name('register');
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

    Route::get('/anwesenheit', function () {
        $user = Auth::user();
        if ($user && $user->hasPermission('anwesenheit')) {
            return view('event-attendee-tracking');}
        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to remove attendance.',
        ], 403);
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
        Route::get('/users/permissions', function (Request $request) {
            $user = Auth::user();
            if ($user && $user->hasPermission('admin')) {
                return (new PermissionController)->getUsersWithPermissions($request);
            }
            abort(403, 'Unauthorized');
        });

        Route::get('/users', function (Request $request) {
            $user = Auth::user();
            if ($user && $user->hasPermission('admin')) {
                return (new UserController)->getUsers($request);
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

        Route::delete('/events/{eventId}/attendees', function ($eventId) {
            $user = Auth::user();
            if ($user && $user->hasPermission('anwesenheit')) {
                return app(ContentController::class)->removeAttendance(request(), $eventId);
            }
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to remove attendance.',
            ], 403);
        });
        
        Route::post('/events/{eventId}/attendees', function ($eventId) {
            $user = Auth::user();
            if ($user && $user->hasPermission('anwesenheit')) {
                return app(ContentController::class)->markAttendance(request(), $eventId);
            }
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to mark attendance.',
            ], 403);
        });
    

    
        Route::get('/events/{eventId}/attendees', [ContentController::class, 'getAttendees']);
    });
    
    // API route for managing events
    Route::prefix('api')->group(function () {
        Route::post('/events', function (Request $request) {
            $user = Auth::user();
            if ($user && $user->hasPermission('posten')) {
                return (new ContentController)->store($request);
            }
            abort(403, 'Unauthorized');
        });

    
        Route::post('/events/{id}', function (Request $request, $id) {
            $user = Auth::user();
            if ($user && $user->hasPermission('posten')) {
                return (new ContentController)->update($request, $id);
            }
            abort(403, 'Unauthorized');
        });
    
        Route::delete('/events/{id}', function ($id) {
            $user = Auth::user();
            if ($user && $user->hasPermission('posten')) {
                return (new ContentController)->destroy($id);
            }
            abort(403, 'Unauthorized');
        });
    });
    
});
Route::get('/list-events', function (Request $request) {
    $user = Auth::user();
    if ($user && $user->hasPermission('posten')) {
        return view('list-events');
    }
    abort(403, 'Unauthorized');
});

Route::get('/edit-event/{id}', function ($id) { 
    $user = Auth::user();
    if ($user && $user->hasPermission('posten')) {
        return view('edit-event', ['eventId' => $id]);
    }
    abort(403, 'Unauthorized');


});


Route::prefix('api')->group(function () {
    Route::get('/events', [ContentController::class, 'index']);
    Route::get('/events/{id}', [ContentController::class, 'getPostApi']);
    Route::get('/events/filter/count', [ContentController::class, 'getPostCount']);

    // Throttled routes
    Route::middleware('throttle:10|10,1')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [UserController::class, 'register']);
        Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });
});




// Logout route
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::view('/registration-success', 'registration-success')->name('registration-success');
Route::view('/email-verification-success', 'email-verification-success')->name('email-verification-success');

Route::get('/verify-email', [App\Http\Controllers\AuthController::class, 'verifyEmail']);

Route::get('/forgot-password', function () {
    return view('forgot-password');
})->middleware('guest')->name('password.request');

Route::get('/reset-password', function () {
    return view('reset-password');
})->middleware('guest')->name('password.reset');






