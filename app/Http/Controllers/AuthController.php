<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
class AuthController extends Controller
{
   
public function login(Request $request)
{
    $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);

    $email = trim(strtolower($request->email));
    $emailHash = hash('sha256', $email); // Use hash for searching
    $user = User::where('email_hash', $emailHash)->first();
    if ($user && Hash::check($request->password, $user->password)) {
        Auth::login($user);
        $request->session()->put('user', $user);
        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
        ], 200);
    } else {
        return response()->json([
            'message' => 'Invalid email or password',
        ], 401);
    }
}

public function logout(Request $request)
{
    // Log the user out of the application
    Auth::logout();

    // Invalidate the session
    $request->session()->invalidate();

    // Regenerate the session token to protect against session fixation attacks
    $request->session()->regenerateToken();

    // Optionally, you can return a response with a success message or redirect
    return response()->json([
        'message' => 'Logout successful',
    ], 200);
}


    public function checkLogin()
    {
        if (Auth::check()) {
            return response()->json([
                'loggedIn' => true,
                'user' => Auth::user()
            ]);
        } else {
            return response()->json([
                'loggedIn' => false
            ]);
        }
    }
}
