<?php

namespace App\Http\Controllers;

use App\Meeting;
use App\User;
use Illuminate\Http\Request;
use JWTAuth;

class RegisterationController extends Controller
{
   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $meeting_id = $request->input('meeting_id');
        $user_id = $request->input('user_id');
        
        $meeting = Meeting::findOrFail($meeting_id);
        $user = User::findOrFail($user_id);

        $response = [
            'msg'=> 'User is already registed for this meeting',
            'user'=>$user,
            'meeting'=>$meeting,
            'unregister'=>[
                'href'=>'api/v1/meeting/registration/' . $meeting->id,
                'method'=>'delete'
            ]
        ];

        if($meeting->users()->where('user_id',$user->id)->first()){
            return response()->json($response,404);
        }

        $user->meetings()->attach($meeting);

        $response = [
            'msg'=> 'You have successfully registed for the meeting',
            'user'=>$user,
            'meeting'=>$meeting,
            'unregister'=>[
                'href'=>'api/v1/meeting/registration/' . $meeting->id,
                'method'=>'delete'
            ]
        ];
        return response()->json($response,201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $meeting = Meeting::findOrFail($id);
        if(!$user=JWTAuth::parseToken()->authenticate()){
            return response()->json([
                'msg'=>'User not found'
            ],404);
        }
        if(!$meeting->users()->where('user_id',$user->id)->first()){
            return response()->json([
                'msg'=>'The User not registed for meeting'
            ],401);
        }
        $meeting->users()->detach($user->id);

        $response = [
            'msg'=>'User unregisted for meeting',
            'meeting'=>$meeting,
            'user'=>$user,
            'register'=>[
                'href'=>'api/v1/meeting/registration/' . $meeting->id,
                'method'=>'POST',
                'params'=>'user_id,meeting_id'
            ]
        ];
        return response()->json($response,200);
    }
}
