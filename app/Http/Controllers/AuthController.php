<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
//register user
public function register(Request $request)
{
    //validate the request
    $attrs = $request->validate([
        'name' => 'required|string',
        'staff_no' => 'required|string|unique:users,staff_no',
        'role_id' => 'required|integer',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6|confirmed'
    ]);
    //create user
    $user = User::create([
        'name' => $attrs['name'],
        'staff_no' => $attrs['staff_no'],
        'role_id' => $attrs['role_id'],
        'email' => $attrs['email'],
        'password' => bcrypt($attrs['password']),
    ]);
    //return response with token
    return response([
        'user' => $user,
        'token' => $user->createToken('secret')->plainTextToken
    ], 200);
}

    // login user
public function login(Request $request)
{
    $attrs = $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:6'
    ]);

    if (!Auth::attempt($attrs)) {
        return response([
            'message' => 'Invalid credentials.'
        ], 403);
    }

    return response([
        'user' => auth()->user(),
        'token' => auth()->user()->createToken('secret')->plainTextToken
    ], 200);
}

    //logout user
    public function logout(Request $request)
    {
     auth()->user()->tokens()->delete();
        return response([
        'message' => 'Logout success.'
    ], 200);
    }

    //get user details
    public function user(Request $request)
    {
        return response([
            'user' => auth()->user()
        ], 200);
    }
}

