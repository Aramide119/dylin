<?php

namespace App\Http\Controllers;

use App\Mail\VerifyEmail;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use App\Models\VerifyUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    //
    public function store(Request $request)
    {
        $validate = $request->validate([
            "first_name" => "required",
            "last_name" => "required",
            "email" => "required|email|unique:users",
            "password"=>"required|min:6"
        ]);

        $user = User::create($validate);

        $expiry = Carbon::now()->addMinutes(5);

        VerifyUser::updateOrCreate([
            'token' => random_int(1000, 9999),
            'user_id' => $user->id,
            'expiry_date' => $expiry,
        ]);

        //send mail
        Mail::to($user->email)->send(new VerifyEmail($user));

        $token = $user->createToken('LaravelAuthApp')->accessToken; 
        if($user){
            $response=[
                'user' => $user,
                'token'=>$token,
                'message'=> 'user created successfully'
            ];
    
            return response()->json($response, 200);
        }else{
            return response()->jsaon(['message' => 'error'], 500);
        }
        
    }

    public function verifyEmail(Request $request)
    {
        $token = $request->input('token');

        $verifyUser = VerifyUser::where('token', $token)->first();

        //check if token has expired
        $currentDate = Carbon::now();

        if($currentDate > $verifyUser->expiry_date){
            $expiry = Carbon::now()->addMinutes(5);

            VerifyUser::updateOrCreate([
                'token' => random_int(1000, 9999),
                'user_id' => $verifyUser->user_id,
                'expiry_date' => $expiry,
            ]);

            $user = User::where('id', $verifyUser->user_id)->first();
            //send mail
            Mail::to($user->email)->send(new VerifyEmail($user));

            $response =[
                'status'=>true,
                'message'=>'Token Expired, Check your mail for another token'
            ];

            return response()->json($response, 200);
        }

        if($verifyUser){
            $user = User::where('id', $verifyUser->user_id)->first();

            $user->email_verified_at = now();
            $user->save();

            VerifyUser::where('user_id', $verifyUser->user_id)->delete();

            $token = $user->createToken('LaravelAuthApp')->accessToken; 
            
                $response=[
                    'user' => $user,
                    'token'=>$token,
                    'message'=> 'user verified successfully'
                ];
        
                return response()->json($response, 200);
        }

        return response()->jason([
            'message' =>'invalid verification token'
        ], 400);

    }

    public function login(Request $request) 
    {
        $attributes = $request->validate([
            "email"=> "required",
            "password"=>"required"
        ]);

        $user = User::where('email', $attributes['email'])->first();

        if($user->email_verified_at == NULL)
        {
            $response=[
                'status' => false,
                'message' =>'verify your email'
            ];

            return response()->json($response, 400);
        }

        if(!Hash::check($attributes['password'], $user->password))
        {
            $response=[
                'status' => false,
                'message' =>'Email or password does not match'
            ];

            return response()->json($response, 400);
        }

        $token = $user->createToken('LaravelAuthApp')->accessToken; 
            
        $response=[
            'user' => $user,
            'token'=>$token,
            'message'=> 'Login Successful'
        ];

        return response()->json($response, 200);
    }

    public function forgotPassword() 
    {

    }
}
