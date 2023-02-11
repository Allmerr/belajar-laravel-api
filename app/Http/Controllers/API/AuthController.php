<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                "success" => false,
                "message" => $validator->errors(),
            ], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            "success" => true,
            "message" => "Register New User",
            "data" => $user,
            "access_token" => $token,
            "token_type" => 'Bearer',
        ], 200);
    }

    public function login(Request $request){
        if(!Auth::attempt($request->only('email','password'))){
            return response()->json([
                "success" => false,
                "message" => "Login Failed",
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            "success" => true,
            "message" => "Login Success",
            "data" => $user,
            "access_token" => $token,
            "token_type" => 'Bearer',
        ], 200);
    }

    public function logout(){
        //the comment below just to ignore intelephense(1013) annoying error.
        /** @var \App\Models\User $user **/

        Auth::user()->tokens->each(function($token, $key) {
            $token->delete();
        });

        return response()->json([
            "success" => true,
            "message" => "Logout Success",
        ], 200);
    }
}