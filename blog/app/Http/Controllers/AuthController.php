<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class AuthController extends Controller
{
   public function store (Request $request)
   {
        $request->validate([
            'name'=>'required',
            'email'=>'required|email',
            'password'=>'required|min:6'
        ]);
        
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');

        $user = new User ([
            'name'=>$name,
            'email'=>$email,
            'password'=>\bcrypt($password)
        ]);

        if($user->save()){
            $user->signin = [
                'href' => 'api/v1/user/signin',
                'method' => 'POST',
                'params' => 'email, password'
            ];

            $response =[
                'msg' => 'User created',
                'user' => $user
            ];

            return response()->json($response,201);
            
        }
        
        $response =[
            'msg' => 'An error occured',
            'user' => $user
        ];

        

        return response()->json($response,404);
   }
   public function signin (Request $request)
   {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = $request->only('email','password');

        try{
            if(!$token = JWTAuth::attempt($credentials)){
                return response()->json([
                    'msg'=>'Invalid cerdentials'
                ],401);
            }
        }catch(JWTException $e){
            return response()->json(['msg'=>'Could not create token'],500);
        }
        return response()->json(['token'=>$token]);
    }

    
}
