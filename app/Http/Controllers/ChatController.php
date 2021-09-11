<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Requests\MessageRequest;
use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class ChatController extends Controller
{

    //@todo implement services and repositories
    //@todo build up queues or rabbitMQ

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $generalRooms = Room::general()->get();
        return view('index', compact('generalRooms'));
    }

    public function fetchMessages()
    {
        return Message::with('user')->get();
    }

    public function sendMessage(MessageRequest $request)
    {
        try {
            if(Request::ajax()){
                DB::beginTransaction();
                $user = User::find(Auth::user()->id);
                $room = Room::whereCode($request->input('room_code'))->first();
                $message = $user->messages()->create([
                    'message' => $request->input('message'),
                    'room_id' => $room->id,
                ]);
                broadcast(new MessageSent($user, $message, $request->input('room_code')))->toOthers();
                DB::commit();
                return response(['status' => 'Message Sent!']);
            }else{
                Log::info('request was not a ajax');
            }
        }
        catch (\Exception $exception){
            DB::rollBack();
            Log::info($exception);
            return response(['status' => 'Message Not Sent!']);
        }
    }

    public function publicChat($room)
    {
        try {
            $room = Room::find(simple_two_way_crypt($room, 'd'));
            $generalRooms = Room::general()->get();
            $messages = $room->load('messages');
            return view('chatbox', ['rooms' => $generalRooms, 'messages' => $messages, 'room_code' => $room->code]);
        }
        catch (\Exception $exception){
            Log::info($exception);
            return redirect()->back();
        }

    }

    public function privateChat($room)
    {
        try {
            $privateRoom = Room::firstOrCreate(['code' => $room], ['name' => null, 'type' => 0]);
            $generalRooms = Room::general()->get();
            $messages = $privateRoom->load('messages');
            return view('chatbox', ['rooms' => $generalRooms, 'messages' => $messages, 'room_code' => $room]);
        }
        catch (\Exception $exception){
            Log::info($exception);
            return redirect()->back();
        }
    }

    public function search(\Illuminate\Http\Request $request)
    {
        if(Request::ajax())
        {
            return $request->all();
        }
    }

}
