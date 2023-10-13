<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfileController extends Controller
{
    //
    public function index() 
    {
        
        //Get Authenticated user id
        $id = Auth::id();

        //Get Authenticated user
        $user = User::findOrFail($id);

        $response =[
            'status' => true,
            'user' => $user,
        ];

        return response()->json($response,200);
    }

    public function update(Request $request, $id)
    {

        $validate = $request->validate([
            "first_name" => "nullable",
            "last_name" => "nullable",
            "email" => "nullable|email|unique:users",
            "password"=>"nullable|min:6"
        ]);

        // Get the authenticated user
        $authenticatedUser = Auth::user();

        // Compare the authenticated user's ID with the requested user's ID
        if ($authenticatedUser->id != $id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
       
        $user = User::where('id', $id)->first();

        $user->update($validate);

        return response()->json(['message' => 'Profile updated successfully']);
    }
}
