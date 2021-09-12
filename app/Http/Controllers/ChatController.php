<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageRequest;
use App\Http\Services\ChatService;
use App\Models\Room;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class ChatController extends Controller
{

    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->middleware('auth');
        $this->chatService = $chatService;
    }

    /**
     * @return Application|ResponseFactory|Factory|View|Response
     */
    public function index()
    {
        try {
            $generalRooms = $this->chatService->generalRooms();
            return view('index', compact('generalRooms'));
        } catch (Exception $exception) {
            Log::info($exception);
            return response(['status', 'retrieving general rooms failed'], '503');
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return Application|ResponseFactory|Response|mixed
     */
    public function fetchMessages(\Illuminate\Http\Request $request)
    {
        try {
            return $this->chatService->fetchRoomMessagesChunked($request->input('room_code'), $request->input('query'));
        } catch (Exception $exception) {
            Log::info($exception);
            return response(['status', 'fetching messages failed'], '503');
        }
    }

    /**
     * @param MessageRequest $request
     * @return Application|ResponseFactory|Response|void
     */
    public function sendMessage(MessageRequest $request)
    {
        try {
            if (Request::ajax()) {
                DB::beginTransaction();
                $this->chatService->sendMessage($request);
                DB::commit();
                return response(['status' => 'Message Sent!'], 200);
            } else {
                Log::info('request was not a ajax');
            }
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception);
            return response(['status' => 'Message Not Sent!'], 503);
        }
    }

    /**
     * @param Room $room
     * @return Application|Factory|View|RedirectResponse
     */
    public function publicChat(Room $room)
    {
        try {
            $generalRooms = $this->chatService->generalRooms();
            $messages = $this->chatService->loadPublicRoom($room);
            return view('chatbox', ['rooms' => $generalRooms, 'messages' => $messages, 'room_code' => $room->code, 'room_name' => $room->name]);
        } catch (Exception $exception) {
            Log::info($exception);
            return redirect()->back();
        }
    }

    /**
     * @param $room
     * @return Application|Factory|View|RedirectResponse
     */
    public function privateChat($room)
    {
        try {
            $generalRooms = $this->chatService->generalRooms();
            $messages = $this->chatService->loadPrivateRoom($room);
            return view('chatbox', ['rooms' => $generalRooms, 'messages' => $messages, 'room_code' => $room, 'room_name' => 'private']);
        } catch (Exception $exception) {
            Log::info($exception);
            return redirect()->back();
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array|Application|ResponseFactory|Response|void
     */
    public function search(\Illuminate\Http\Request $request)
    {
        try {
            if (Request::ajax()) {
                return $this->chatService->chatSearch($request);
            } else {
                Log::info('that was not a ajax request');
            }
        } catch (Exception $exception) {
            Log::info($exception);
            return response(['status' => 'Search has been filed !'], 503);
        }
    }
}
