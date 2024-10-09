<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:4',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(['message' => 'Registered Successfully', 'token' => $token]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credential = $request->only(['email', 'password']);
        $token = JWTAuth::attempt($credential);
        $user = JWTAuth::user();
        if (!$token) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        };

        return response()->json(['token' => $token, 'user' => $user], 200);
    }

    public function profile()
    {
        $user = JWTAuth::user();
        if ($user) {
            return response()->json($user, 200);
        } else {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function deleteProfile()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        $user = JWTAuth::user();
        $user->delete();
        return response()->json([
            'message' => 'User Profile Deleted Succesfully'
        ], 200);
    }

    public function sendOtp(){
        
    }

}
