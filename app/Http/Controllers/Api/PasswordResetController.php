<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    public function sendPasswordResetEmail(Request $request)
    {
        // 1) check whether user exists or not 
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 0, 'errors' => $validator->errors()->toArray()]);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['msg' => 'Cannot find user with the provided email']);
        }

        // Generate Token 
        $token = Str::random(60);

        // Saving data to the password reset table 
        PasswordReset::create([
            'email' => $request->email,
            'token' => $token
        ]);

        Mail::to($request->email)->send(new PasswordResetMail($token));
        return response()->json(['msg' => 'Password reset link sent successfully.']);
    }

    public function resetPassword(Request $request, $token)
    {
        //delete token older than 30 minutes
        $formatted = Carbon::now()->subMinutes(30)->toDateTimeString();
        PasswordReset::where('created_at', '<=', $formatted)->delete();
        $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed'
        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 0, 'errors' => $validator->errors()->toArray()]);
        }
        $passwordReset = PasswordReset::where('token', $token)->first();
        if (!$passwordReset) {
            return response()->json(['msg' => 'Invalid or expired token']);
        }
        $user = User::where('email', $passwordReset->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();
        // delete the token after resetting password
        PasswordReset::where('email', $user->email)->delete();

        return response()->json(['msg' => 'password reset successfully.']);
    }
}
