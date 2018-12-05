<?php

namespace App\Http\Controllers;

use App\Meeting;
use App\User;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class MeetingController extends Controller
{

    public function __construct()
    {
        $this->middleware('jwt.auth',['only'=>[
            'update','store','destroy']
        ]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $meetings = Meeting::all();

        foreach ($meetings as $meeting){
            $meeting->view_meeting = [
                'href' => 'api/v1/meeting/' . $meeting->id,
                'method' => 'GET'
            ];
        }

        $resposne = [
            'msg' => 'List of all meetings',
            'meetings' => $meetings
        ];
    }

    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'time' => 'required|date_format:Y-m-d',
        ]);

        if(! $user = JWTAuth::parseToken()->authentication()){
            return response()->json([
                'msg'=>'User not found'
            ],404);
        }

        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id =$user->id;

        $meeting = new Meeting([
            'title'=> $title,
            'description'=> $description,
            'time'=> $time                                          //Carbon::createFormFormat('YmdHie',$time),
        ]);

        if($meeting->save()){
            $meeting->users()->attach($user_id);
            $meeting->view_meeting = [
                'href'=>'api/v1/meeting/' . $meeting->id,
                'method'=>'GET'
            ];
        }

        $response = [
            'msg' => 'Meeting created',
            'meeting' => $meeting
        ];

        return response()->json($response,201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $meeting = Meeting::with('users')->where('id',$id)->firstOrFail();

        $meeting->view_meeting = [
            'href'=>'api/v1/meeting/' . $meeting->id,
            'method'=>'GET'
        ];

        $response = [
            'msg'=>'Meeting Information',
            'meeting'=>$meeting
        ];

        return response()->json($response,201);

    }

    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'time' => 'required|date_format:Y-m-d',
        ]);

        if(! $user = JWTAuth::parseToken()->authenticate()){
            return response()->json([
                'msg'=>'User not found'
            ],404);
        }


        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $user->id;

        $meeting = Meeting::with('users')->findOrFail($id);
        if(!$meeting->users()->where('user_id',$user_id)->first()){
            return response()->json([
                'msg'=>'User not registed for this meeting'
            ],401);
        }
        $meeting->title = $title;
        $meeting->description = $description;
        $meeting->time = $time;
        $meeting->users()->user_id = $user_id;

        if(!$meeting->update()){
            return response()->json([
                'msg'=>'Error during updating'
            ],404);
        }

        $meeting->view_meeting = [
            'href'=>'api/v1/meeting/' . $id,
            'method'=>'GET'
        ];

        $response = [
            'msg'=>'The meeting is successfully updated',
            'meeting'=>$meeting
        ];

        return response()->json($response,200);
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
        

        if(! $user = JWTAuth::parseToken()->authentication()){
            return response()->json([
                'msg'=>'User not found'
            ],404);
        }

        if(!$meeting->users()->where('user_id',$user->id)->first()){
            return response()->json([
                'msg'=>'User not registed for this meeting'
            ],401);
        }

        $users = $meeting->users;
        $meeting->delete();
        $meeting->users()->detach();
        if(!$meeting->delete()){
            foreach($users as $user){
                $meeting->users()->attach($user);
            }
            return response()->json([
                'msg'=>'Deletion Failed',

            ],404);
        }
        $response = [
            'msg'=>'Meeting deleted',
            'create'=> [
                'href'=>'api/v1/meeting',
                'method'=>'POST',
                'params'=>'title,description,time'
            ]
            ];

    }
}
