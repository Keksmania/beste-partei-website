<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $email = trim(strtolower($request->email));
        $encryptedEmail = Crypt::encryptString($email);
        $emailHash = hash('sha256', $email);

        // Check if the email hash already exists
        if (User::where('email_hash', $emailHash)->exists()) {
            return response()->json([
                'message' => 'This email is already registered.',
            ], 409);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',

            ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $encryptedEmail,
            'email_hash' => $emailHash, // Store hash for searching
            'password' => Hash::make($request->password),
            'activated' => false, // Default to false
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    public function getRegistrations(Request $request)
    {
        $status = $request->get('status', 'all');
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);
        $offset = ($page - 1) * $perPage;

        $query = User::query();

        if ($status === 'approved') {
            $query->where('activated', true);
        } elseif ($status === 'pending') {
            $query->where('activated', false);
        }

        $total = $query->count();

        $users = $query->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // Decrypt the email addresses
        $users->transform(function ($user) {
            $user->email = Crypt::decryptString($user->email);
            return $user;
        });

        return response()->json(['users' => $users, 'total' => $total]);
    }

    public function approveRegistration($id)
    {
        $user = User::findOrFail($id);
        $user->activated = true;
        $user->save();

        return response()->json(['message' => 'User approved successfully.']);
    }
}
