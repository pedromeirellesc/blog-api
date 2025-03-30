<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function register(Request $request)
    {

        $fields = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);

        $user = User::create($fields);

        $token = $user->createToken($request->name);

        return [
            'user' => $user,
            'token' => $token->plainTextToken
        ];
    }

    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return [
                'message' => 'The provided credentials are incorrect.'
            ];
        }

        $token = $user->createToken($user->name);

        return [
            'user' => $user,
            'token' => $token->plainTextToken
        ];
    }

    public function logout(Request $request)
    {

        $request->user()->tokens()->delete();

        return [
            'message' => "You're logged out."
        ];
    }

    public function show(int $id)
    {

        try {
            $user = User::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response([
                'status' => 'error',
                'error' => "Register #{$id} not found."
            ], 404);
        }

        $user->load(['posts' => function ($query) {
            $query->orderBy('created_at', 'DESC');
        }, 'comments' => function ($query) {
            $query->orderBy('created_at', 'DESC');
        }]);

        return response()->json([
            'user' => new UserResource($user)
        ], 200);
    }
}
