<?php

namespace App\Http\Controllers;

use App\Events\MessagesUpdated;
use App\Message;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function all(): Response
    {
        $threads = Message::Where('from_admin', false)->orderByDesc('created_at')->get()->groupBy('user_id');
        // Format response correctly
        $response = [];
        foreach ($threads as $thread) {
            $arr['message_id'] = $thread[0]->id;
            $arr['user_id'] = $thread[0]->user->id;
            $arr['name'] = $thread[0]->user->name;
            $arr['lastMessage'] = $thread[0]->message;
            $arr['new'] = !$thread[0]->opened;
            $arr['email'] = $thread[0]->user->email;
            $arr['date'] = date_format($thread[0]->updated_at,"H:i d M Y ");
            array_push($response, $arr);
        }
        $response = json_encode($response, true);
        return new Response($response , Response::HTTP_OK);
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function readMessages($userId=null)
    {
        if(Auth::user()->is_admin) {
            // Update unread
            $unreadFromUser = Message::Where('user_id', $userId)->where('opened', false)->get();
            foreach ($unreadFromUser as $msg) {
                $msg->opened = true;
                $msg->save();
            }
            $messages = Message::Where('user_id', $userId)->get();
        } else {
            $messages = Message::Where('user_id', Auth::user()->id)->get();
        }

        // Format response correctly
        $response = [];
        foreach ($messages as $msg) {
            $arr['message_id'] = $msg->id;
            $arr['user_id'] = $msg->user->id;
            $arr['message'] = $msg->message;
            $arr['admin'] = $msg->from_admin;
            $arr['new'] = !$msg->opened;
            $arr['timestamp'] = date_format($msg->updated_at,"H:i d M Y ");
            array_push($response, $arr);
        }

        $response = json_encode($response, true);
        return new Response($response , Response::HTTP_OK);
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function newMessage(Request $request, $userId=null)
    {
        $request->validate([
           'message' => 'required|string'
       ]);
        if(Auth::user()->is_admin) {
            $message = Message::create([
               'user_id' => $userId,
               'message' => $request->input('message'),
               'from_admin' => true,
               'opened' => true
           ]);
        } else {
            $message = Message::create([
               'user_id' => Auth::user()->id,
               'message' => $request->input('message'),
               'from_admin' => false
           ]);
        }
        $packet['message_id'] = $message->id;
        $packet['message'] = $message->message;
        $packet['from_admin'] = Auth::user()->is_admin;
        $packet['action'] = 'new';
        $packet['opened'] = $message->opened;
        $packet['user_id'] = $message->user->id;
        $packet['name'] = $message->user->name;
        $packet['email'] = $message->user->email;
        $packet['created_at'] =  date_format($message->created_at,"H:i d M Y ");

        MessagesUpdated::dispatch($packet, $message->user);

        return new Response("Success!" , Response::HTTP_OK);
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function updateMessage(Request $request, $id=null)
    {
        $request->validate([
           'message' => 'required|string'
       ]);
        $message = Message::findOrFail($id);
        if ($message->from_admin && Auth::user()->is_admin) {
            $message->message = $request->message;
            $message->save();
        } else if(!$message->from_admin && Auth::user()){
            $message->message = $request->message;
            $message->save();
        } else {
            return new Response("You do not have permission" , Response::HTTP_FORBIDDEN);
        }

        $packet['message_id'] = $message->id;
        $packet['message'] = $message->message;
        $packet['action'] = 'edit';
        $packet['from_admin'] = $message->from_admin;
        $packet['opened'] = $message->opened;
        $packet['user_id'] = $message->user->id;
        $packet['name'] = $message->user->name;
        $packet['email'] = $message->user->email;
        $packet['created_at'] =  date_format($message->created_at,"H:i d M Y ");

        MessagesUpdated::dispatch($packet, $message->user);

        return new Response("Success!" , Response::HTTP_OK);
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function deleteMessage($id=null)
    {
        $message = Message::find($id);
        if ($message->from_admin && Auth::user()->is_admin) {
            $message->delete();
        } else if(!$message->from_admin && Auth::user()){
            $message->delete();
        } else {
            return new Response("You do not have permission" , Response::HTTP_FORBIDDEN);
        }

        $packet['message_id'] = $id;
        $packet['message'] = $message->message;
        $packet['action'] = 'delete';
        $packet['from_admin'] = $message->from_admin;
        $packet['opened'] = $message->opened;
        $packet['user_id'] = $message->user->id;
        $packet['name'] = $message->user->name;
        $packet['email'] = $message->user->email;
        $packet['created_at'] =  date_format($message->created_at,"H:i d M Y ");

        MessagesUpdated::dispatch($packet, $message->user);

        return new Response("Success!" , Response::HTTP_OK);
    }
}
