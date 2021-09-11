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

    public function fetchMessages(\Illuminate\Http\Request $request)
    {
        try {
            $room = Room::find(simple_two_way_crypt($request->input('room_code'), 'd'));
            $messages = $room->setRelation('messages', $room->messages()->latest()->where('id', '<', $request->input('query'))->with('user')->orderBy('id', 'DESC')->take(15)->get());
            return $messages;
        }catch (\Exception $exception){

        }
    }

    public function sendMessage(MessageRequest $request)
    {
        try {
            if (Request::ajax()) {
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
            } else {
                Log::info('request was not a ajax');
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($exception);
            return response(['status' => 'Message Not Sent!']);
        }
    }

    # I know what is "route model binding" but this time it would not help :)
    public function publicChat($room)
    {
        try {
            $room = Room::find(simple_two_way_crypt($room, 'd'));
            $generalRooms = Room::general()->get();
            $messages = $room->setRelation('messages', $room->messages()->latest()->take(15)->get());
            return view('chatbox', ['rooms' => $generalRooms, 'messages' => $messages, 'room_code' => $room->code, 'room_name' => $room->name]);
        } catch (\Exception $exception) {
            Log::info($exception);
            return redirect()->back();
        }

    }

    # I know what is "route model binding" but this time it would not help :)
    public function privateChat($room)
    {
        try {
            $privateRoom = Room::firstOrCreate(['code' => $room], ['name' => null, 'type' => 0]);
            $generalRooms = Room::general()->get();
            $messages = $privateRoom->load('messages');
            return view('chatbox', ['rooms' => $generalRooms, 'messages' => $messages, 'room_code' => $room, 'room_name' => 'private']);
        } catch (\Exception $exception) {
            Log::info($exception);
            return redirect()->back();
        }
    }

    public function search(\Illuminate\Http\Request $request)
    {
        try {
            if (Request::ajax()) {
                $query = $request->input('query');

                $users = User::where('chat_name', 'like', '%' . $query . '%')->get(['id', 'chat_name']);
                $mapped_users = collect($users)->map(function ($item) {
                    return [
                        'name' => $item->chat_name,
                        'link' => route('room.private', ['room' => createPrivateRoomCode($item->id)]),
                        'type' => 'user'
                    ];
                })->toArray();

                $rooms = Room::general()->where('name', 'like', '%' . $query . '%')->get(['name', 'code']);
                $mapped_rooms = collect($rooms)->map(function ($item) {
                    return [
                        'name' => $item->name,
                        'link' => route('room.private', ['room' => $item->code]),
                        'type' => 'room'
                    ];
                })->toArray();

                return array_merge($mapped_users, $mapped_rooms);

            } else {
                Log::info('that was not a ajax request');
            }
        } catch (\Exception $exception) {
            Log::info($exception);
        }
    }

}
