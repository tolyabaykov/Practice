<?php

namespace App\Http\Controllers;

use App\Answer;
use App\Events;
use App\Notifications\NotiAnswer;
use App\Message;
use App\Notifications\NotiToMessage;
use App\Status;
use App\Theme;
use App\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        return Message::all();
    }

    // Вывод сообщений
    public function showMessages(Request $request, $id)
    {   $users = User::all();
    $events = Events::all();
    $statuses = Status::all();
        $theme = Theme::find($id);
        $messags = Message::all();
        $messages = $theme->messages()->paginate(5);
        if ($request->ajax()) {
            return view('messages', compact('messages'));
        }
        return view('theme', ['messags'=> $messags, 'users'=>$users,'statuses'=>$statuses,'events'=>$events,'theme' => $theme, 'messages' => $messages]);

    }


    // Вывод сообщений для ajax
    public function ajaxMessages(Request $request, $id)
    {
        $theme = Theme::find($id);;
        $messages = $theme->messages()->paginate(5);
        return ($messages);
    }

//создание сообщений
    public function store(Request $request)
    {
        $users=User::all();
        $theme = Theme::find($request->theme_id);
        Message::create([
                'user_id' => auth()->user()->id
            ] + $request->all());
        $messages = Theme::find($request->theme_id)->messages()->paginate(5);
//создание уведомления
        foreach ($users as $user){
            if ($user->id==$theme->owner_id){
                $user->notify(new NotiToMessage($user));
            }
        }
        return view('messages', ['messages' => $messages]);

    }

//ответ на сообщение
    public function store_answer(Request $request, $id)
    {


//       $id->addAnswer($request['body']);
        $messages = Message::find($id); //Почему то без этого не работает
       Answer::create(
            [
                'body' => $request['body'],
                'user_id' => auth()->user()->id,
                'messages_id' => $messages->id,

            ]
        );
        //создание уведомления
        $users=User::all();
        foreach ($users as $user){
            if ($user->id==$messages->user_id){
                $user->notify(new NotiAnswer($user));
            }
        }
        return redirect()->back();
    }

    public function show(Message $message)
    {
        return view('showMessage', ['message' => $message]);
    }

//редактирование сообщения
    public function update($id, Request $request)
    {
        $messeges = Message::find($id);
        $messeges->fill($request->all());
        $messeges->save();
        return redirect()->back();
    }

//удаление сообщения
    public function destroy(Message $message)
    {
        return $message->delete();
    }

}
