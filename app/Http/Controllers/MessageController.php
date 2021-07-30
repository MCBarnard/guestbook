<?php

namespace App\Http\Controllers;

use App\Events\MessagesUpdated;
use App\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        MessagesUpdated::dispatch($message, $message->user, 'new');

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
        $message = Message::find(intval($id));
        if ($message->from_admin && Auth::user()->is_admin) {
            $message->message = $request->message;
            $message->save();
        } else if(!$message->from_admin && Auth::user()){
            $message->message = $request->message;
            $message->save();
        } else {
            return new Response("You do not have permission" , Response::HTTP_FORBIDDEN);
        }

        MessagesUpdated::dispatch($message, $message->user, 'edit');

        return new Response("Success!" , Response::HTTP_OK);
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function deleteMessage($id=null)
    {
        $message = Message::where('id', $id)->first();
        $messageThatWasDeleted = $message;
        if ($message->from_admin && Auth::user()->is_admin) {
            $message->delete();
        } else if(!$message->from_admin && Auth::user()){
            $message->delete();
        } else {
            return new Response("You do not have permission" , Response::HTTP_FORBIDDEN);
        }

        MessagesUpdated::dispatch($messageThatWasDeleted, $messageThatWasDeleted->user, 'delete');

        return new Response("Success!" , Response::HTTP_OK);
    }
}
