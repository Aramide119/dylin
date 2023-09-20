<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use App\Models\VerifyUser;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;
use App\Mail\ResetPasswordEmail;
use App\Models\ForgotPassword;

class ForgotPasswordController extends Controller
{
    public function sendResetEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // set token expiry date
        $expiry =  Carbon::now()->addMinutes(10);

        // Store the reset token in the forgot_passwords table
        $send_token = ForgotPassword::updateOrCreate([
            'user_id' => $user->id,
            'token' => random_int(1000, 9999),
            'expiry_date' => $expiry,
        ]);

        // Send the reset email to the user
        Mail::to($user->email)->send(new ResetPasswordEmail($send_token->token, $user));

        if ($user) {
            $response=[
                'status' => true,
                'message' => 'Password reset email sent.'
            ];

            

            return response()->json($response, 200);
        }else{
            return response()->json(['message' => 'error'], 500);
        }

    }


    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();
        
        // Find the email and token in the password_resets table
        $passwordReset = ForgotPassword::where('user_id', $user->id)
            ->where('token', $request->token)
            ->first();

        if (!$passwordReset) {
            return response()->json(['message' => 'Invalid reset token.'], 400);
        }


        if (!$user) {

            $response =[
                'status' => true,
                'message' => 'User Not Found'
            ];

            return response()->json($response,404);
        }

        //check if token has expired
        $currentDate = Carbon::now();
        if($currentDate > $passwordReset->expiry_date){
            $expiry =  Carbon::now()->addMinutes(10);

        $send_token = ForgotPassword::updateOrCreate([
                'token' => random_int(1000, 9999),
                'user_id' => $user->id,
                'expiry_date' => $expiry,
            ]);
        
        //send email to  
        
            Mail::to($user->email)->send(new ResetPasswordEmail($send_token->token, $user));

            $response =[
                'status' => true,
                'message' => 'Token Expired! Kindly check email for another token'
            ];

            return response()->json($response,200);
        }

        // Update the user's password
        $user->password = bcrypt($request->password);
        $user->save();

        // Delete the password reset record
        $passwordReset->delete();

        $response =[
            "status" => true,
            "message" => "Password Reset Successful"
        ];
        return response()->json($response, 200);
    }
}
