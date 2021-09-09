<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{

    //@todo implement services and repositories
    //@todo build up queues
    //@todo implement gate and policies

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $rooms = Room::general()->get();
        return view('index', compact('rooms'));
    }

    public function fetchMessages()
    {
        return Message::with('user')->get();
    }

    public function sendMessage(Request $request)
    {
        try {

            DB::beginTransaction();
            $user = User::find(Auth::user()->id);
            $message = $user->messages()->create([
                'message' => $request->input('message'),
                'room_id' => $request->input('room_id')
            ]);
            broadcast(new MessageSent($user, $message))->toOthers();
            DB::commit();
            return response(['status' => 'Message Sent!']);
        }
        catch (\Exception $exception){
            DB::rollBack();
            Log::info($exception);
            return response(['status' => 'Message Not Sent!']);
        }

    }

    public function loadRoom(Room $room)
    {
        $rooms = Room::general()->get();
        $messages = $room->load('messages');
        return view('chatbox', ['rooms' => $rooms, 'messages' => $messages, 'room_id' => $room->id]);
    }

    public function privateChat(User $user)
    {
        $room = DB::table('room_user')->where('room_id', );
    }

}
