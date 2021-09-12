<?php

namespace App\Http\Services;

use App\Events\MessageSent;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ChatService
{

    public const CHUNK = 15;

    /**
     * @return mixed
     */
    public function generalRooms()
    {
        return Room::general()->get();
    }

    /**
     * @param $room_code
     * @return mixed
     */
    public function findPrivateRoomByCode($room_code)
    {
        return Room::find(simple_two_way_crypt($room_code, 'd'));
    }

    /**
     * @param $room_code
     * @return mixed
     */
    public function findPublicRoomByCode($room_code)
    {
        return Room::whereCode($room_code)->first();
    }

    /**
     * @param $room_code
     * @param $query
     * @return mixed
     */
    public function fetchRoomMessagesChunked($room_code, $chunk_id)
    {
        $room = $this->findPrivateRoomByCode($room_code);
        return $room->setRelation('messages',
                $room->messages()
                ->latest()
                ->where('id', '<', $chunk_id)
                ->with('user')
                ->orderBy('id', 'DESC')
                ->take(SELF::CHUNK)
                ->get()
        );
    }

    /**
     * @param Room $room
     * @return Room
     */
    public function fetchPublicRoomMessages(Room $room)
    {
        return $room->setRelation('messages',
            $room->messages()
            ->latest()
            ->with('user')
            ->orderBy('id', 'DESC')
            ->take(SELF::CHUNK)
            ->get()
        );
    }

    /**
     * @param $room
     * @return mixed
     */
    public function fetchPrivateRoomMessages($room)
    {
        $room = Room::firstOrCreate(['code' => $room], ['name' => null, 'type' => 0]);
        return $room->setRelation('messages',
            $room->messages()
            ->latest()
            ->with('user')
            ->orderBy('id', 'DESC')
            ->take(SELF::CHUNK)
            ->get()
        );
    }

    /**
     * @param $request
     */
    public function sendMessage($request)
    {
        $user = Auth::user();
        $room = $this->findPublicRoomByCode($request->input('room_code'));
        $message = $user->messages()->create([
            'message' => $request->input('message'),
            'room_id' => $room->id,
        ]);
        $this->broadcastMessage($user, $message, $request->input('room_code'));
    }

    /**
     * @param $user
     * @param $message
     * @param $room_code
     */
    public function broadcastMessage($user, $message, $room_code)
    {
        broadcast(new MessageSent($user, $message, $room_code))->toOthers();
    }

    /**
     * @param $room
     * @return Room
     */
    public function loadPublicRoom($room)
    {
        return $this->fetchPublicRoomMessages($room);
    }

    /**
     * @param $room
     * @return mixed
     */
    public function loadPrivateRoom($room)
    {
        return $this->fetchPrivateRoomMessages($room);
    }

    /**
     * @param $request
     * @return array
     */
    public function chatSearch($request)
    {
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
    }

}
