<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use App\Mail\ResetPasswordMail;
use RealRashid\SweetAlert\Facades\Alert; 

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
    
        if ($user) {
            // Check if the user is activated
            if (!$user->activated) {
                return response()->json([
                    'message' => 'Your account is not activated. Please contact the administrator.',
                ], 403); // Forbidden response
            }
    
            // Check if the email is verified
            if (!$user->email_verified_at) {
                return response()->json([
                    'message' => 'Your email is not verified. Please verify your email before logging in.',
                ], 403); // Forbidden response
            }
    
            // Verify the password
            if (Hash::check($request->password, $user->password)) {
                Auth::login($user);
                $request->session()->put('user', $user);
    
    
                return redirect('/');
            }
        }
    
        return response()->json([
            'message' => 'Invalid email or password',
        ], 401); // Unauthorized response
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
 



    public function verifyEmail(Request $request)
    {
        $key = $request->get('key');

        $user = User::where('verification_key', $key)->first();

        if (!$user) {
            return redirect('/')->with('error', 'Invalid verification key.');
        }

        $user->update([
            'email_verified_at' => now(),
            'verification_key' => null, // Invalidate the key
        ]);

        return redirect('/email-verification-success')->with('success', 'Email verified successfully!');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
    
        // Process email: trim, lowercase, hash
        $email = trim(strtolower($request->email));
        $emailHash = hash('sha256', $email);
    
        // Find user by email hash
        $user = User::where('email_hash', $emailHash)->first();
    
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email konnte nicht gefunden werden.'
            ], 404);  // Return error if the user is not found
        }
    
        // Generate a reset token
        $resetToken = Str::random(64);
    
        // Store the reset token and timestamp in the password_resets table (ensure it exists)
        DB::table('password_resets')->updateOrInsert(
            ['email_hash' => $emailHash], // Use email_hash instead of plain email
            [
                'token' => Hash::make($resetToken),
                'created_at' => now(),
            ]
        );
    
        // Send reset email
        if (!config('app.debug')) {
            $resetUrl = url("/reset-password?token={$resetToken}&email={$email}");
            Mail::to(Crypt::decryptString($user->email))->send(new ResetPasswordMail($user->firstname, $resetUrl));
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Wir haben Ihnen einen Link zum Zurücksetzen des Passworts gesendet.'
        ], 200);  // Return success message
    }
// Reset password
public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'token' => 'required',
        'password' => 'required|confirmed|min:8',
    ]);

    // Process email: trim, lowercase, hash
    $email = trim(strtolower($request->email));
    $emailHash = hash('sha256', $email);

    // Find reset token in password_resets table
    $reset = DB::table('password_resets')->where('email_hash', $emailHash)->first();

    if (!$reset) {
        return response()->json(['status' => 'error', 'message' => 'Ungültiger Reset-Token oder Email.'], 400);
    }

    // Verify the reset token
    if (!Hash::check($request->token, $reset->token)) {
        return response()->json(['status' => 'error', 'message' => 'Ungültiger Reset-Token.'], 400);
    }

    // Check token expiry (e.g., 1 hour validity)
    if (now()->diffInMinutes($reset->created_at) > 60) {
        return response()->json(['status' => 'error', 'message' => 'Reset-Token ist abgelaufen.'], 400);
    }

    // Find user and update password
    $user = User::where('email_hash', $emailHash)->first();

    if (!$user) {
        return response()->json(['status' => 'error', 'message' => 'Benutzer konnte nicht gefunden werden.'], 404);
    }

    // Update password
    $user->forceFill(['password' => Hash::make($request->password)])->save();

    // Delete the used reset token
    DB::table('password_resets')->where('email_hash', $emailHash)->delete();

    // Return success response
    return response()->json(['status' => 'success', 'message' => 'Passwort erfolgreich zurückgesetzt.']);
}


}
