<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
        ]);
        if ($validator->fails())
            return response()->json(['code' => 0, 'error' => $validator->errors()->toArray()]);

        // Retrieve the validated input...
        $validated = $validator->validated();
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        // sending verification mail 
        Mail::to($user->email)->send(new VerifyEmail($user));
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token, 'token_type' => 'Bearer', 'user' => $user]);
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
        if ($validator->fails())
            return response()->json(['code' => 0, 'error' => $validator->errors()->toJson()]);

        // Retrieve the validated input...
        $validated = $validator->validated();
        $user = User::where('email', $validated['email'])->first();
        if ($user && Hash::check($validated['password'], $user->password)) {
            // return $request->user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json(['token' => $token, 'token_type' => 'Bearer', 'user' => $user]);
        }
        return response()->json(['msg' => 'Invalid credentials'], 401);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:8',
            'new_password' => 'required|confirmed|min:8',
        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 0, 'error' => $validator->errors()->toJson()]);
        }

        $user = auth()->user();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['msg' => 'Incorrect current password']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        $user->tokens()->delete();

        return response()->json(['msg' => 'Password changed successfully.']);
    }

    public function userData()
    {
        return response()->json(['msg' => 'User data can be accessed here.']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['status' => 'success']);
    }
}
