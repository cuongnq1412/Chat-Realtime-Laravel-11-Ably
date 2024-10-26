<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Models\User;

class ChatController extends Controller
{
    public function loadDashboard(){
        // fetch them here except the logged in user as user should not see him or herself in a list
        $all_users = User::where('user_id','!=',auth()->user()->user_id)->get();
        return view('dashboard',compact('all_users'));
    }

    public function CheckConversation(Request $request){
        $recipientId = $request->recipientId;
        $loggedInUserId = auth()->user()->user_id;


        $Conversation = Conversation::where(function ($query) use ($recipientId, $loggedInUserId) {
            $query->where('user1_id', $loggedInUserId)
                ->where('user2_id', $recipientId);
        })->orWhere(function ($query) use ($recipientId, $loggedInUserId) {
            $query->where('user1_id', $recipientId)
                ->where('user2_id', $loggedInUserId);
        })->first();

        if ($Conversation) {
            return response()->json([
                'channelExists' => true,
                'channelName' => $Conversation->conversation_name,
            ]);
        } else {
            return response()->json([
                'channelExists' => false,
            ]);
        }
}



    public function CreateConversation(Request $request){
        $recipientId = $request->recipientId;
        $loggedInUserId = auth()->user()->user_id;
        try {
            // Generate the channel name
            $channelName = 'chat-' . min($recipientId, $loggedInUserId) . '-' . max($recipientId, $loggedInUserId);


            // Create the channel in the database
            $channel = Conversation::create([
                'user1_id' => $loggedInUserId,
                'user2_id' => $recipientId,
                'conversation_name' => $channelName,
            ]);


            return response()->json([
                'success' => true,
                'channelName' => $channelName,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
