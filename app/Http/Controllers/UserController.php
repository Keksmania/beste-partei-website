<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class UserController extends Controller
{
    public function register(Request $request)
{
    $email = trim(strtolower($request->email));
    $encryptedEmail = Crypt::encryptString($email);
    $emailHash = hash('sha256', $email);

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $encryptedEmail,
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $encryptedEmail,
        'email_hash' => $emailHash, // Store hash for searching
        'password' => Hash::make($request->password),
    ]);

    return response()->json([
        'message' => 'User created successfully',
        'user' => $user,
    ], 201);
}
}
