<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function Login(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Retrieve the email and password from the form
        $email = $request->input('email');
        $password = $request->input('password');

        // Debugging to confirm the method is being called
        return view("start");
    }
}
