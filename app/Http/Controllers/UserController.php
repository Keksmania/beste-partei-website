<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use App\Mail\EmailVerificationMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
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
                'message' => 'Diese Email ist schon registriert!',
            ], 409);
        }

        // Validate input
        $request->validate([
            'firstname' => 'required|string|max:255', // Added firstname validation
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'captcha' => 'required|string',
            'captcha_fruit' => 'required|string',
        ]);

        // Validate CAPTCHA
        $expectedCaptcha = strrev($request->captcha_fruit);
        if ($request->captcha !== $expectedCaptcha) {
            return response()->json([
                'message' => 'CAPTCHA falsch. Probiere es nochmal.',
            ], 400); // Bad request
        }

        $user = User::create([
            'firstname' => $request->firstname, // Storing firstname
            'name' => $request->name,
            'email' => $encryptedEmail,
            'email_hash' => $emailHash,
            'password' => Hash::make($request->password),
            'activated' => false,
            'verification_key' => Str::random(64),
        ]);

        if (!config('app.debug')) {
            $verificationUrl = url("/verify-email?key={$user->verification_key}");
            Mail::to($email)->send(new EmailVerificationMail($user->firstname, $verificationUrl)); // Use firstname in email
        }
        return response()->json([
            'message' => 'Benutzer wurde erstellt',
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

        return response()->json(['message' => 'Benutzer wurde bestätigt.']);
    }


    public function getUsers(Request $request)
    {
        // Fetch query parameters for search and pagination
        $search = $request->input('search', '');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
    
        // Query to fetch users with optional search by name
        $query = User::query();
    
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%'); // Added email search
            });
        }
    
        // Paginate the results and select only required fields
        $users = $query->select('firstname', 'name', 'email', 'id')->paginate($perPage, ['*'], 'page', $page);
    
        // Decrypt email and return only necessary fields
        $usersFormatted = $users->map(function ($user) {
            return [
                'id'=> $user->id,
                'firstname' => $user->firstname,
                'name' => $user->name,
                'email' => Crypt::decryptString($user->email),
            ];
        });
    
        // Return the response
        return response()->json([
            'users' => $usersFormatted,
            'total' => $users->total(),
            'current_page' => $users->currentPage(),
            'per_page' => $users->perPage(),
        ]);
    }
    
}
