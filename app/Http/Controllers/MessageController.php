<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Constant;
use \Firebase\FirebaseLib;
use Auth;
use \App\User;
use \App\Issue;
use \App\UserIssue;

class MessageController extends Controller
{
    public function getMessageIndex(Request $request){
        $user = Auth::user();
        // \setcookie(Constant::USER_CURRENT_ID, Auth::user()->id);
        // \Cookie::queue(Constant::USER_CURRENT_ID, Auth::user()->id, 10);

        $targetUserIssue = null;
        $myUserIssue = null;
        if(isset($request->targetId)){
            $targetUserIssue = UserIssue::find(\base64_decode($request->targetId));
            $targetUserIssue->nickname = $targetUserIssue->nickname()->first();
            $targetUserIssue->issueTitle = $targetUserIssue->issue()->first()->title;
            $myUserIssue = UserIssue::where('issue_id', $targetUserIssue->issue_id)->where('user_id', $user->id)->first();
            $myUserIssue->issue = $myUserIssue->issue()->first();
            $myUserIssue->nickname = $myUserIssue->nickname()->first();
        }

        $firebase = new FirebaseLib(Constant::getFirebaseDefaultURL(), Constant::FIREBASE_DEFAULT_TOKEN);
        $allMessage = $firebase->get(Constant::FIREBASE_DEFAULT_PATH_MESSAGE.
                'user_'.$user->id.'/');
        $allMessage = \json_decode($allMessage);

        if($allMessage != null){
            foreach($allMessage as $issueId => $issue){
                if(\explode('_', $issueId)[0] == 'issue'){
                    $tempIssue = Issue::find(\explode('_', $issueId)[1]);
                    foreach($issue as $userId => $userIssue){
                        $userId = \explode('_', $userId)[1];
                        $tempUserIssue = UserIssue::where('issue_id', $tempIssue->id)->where('user_id', $userId)->first();
                        $tempUserIssue->nickname = $tempUserIssue->nickname()->first();
                        $tempUserIssue->issueTitle = $tempUserIssue->issue()->first()->title;
                        $userIssue->userIssue = $tempUserIssue;
                    }
                    // $issue->issue = $tempIssue;
                }
            }
        }

        return view('message.index', [
            'allMessage' => $allMessage,
            'targetUserIssue' => $targetUserIssue,
            'myUserIssue' => $myUserIssue
        ]);
    }

    public function postMessageContent(Request $request){
        $myselfUserIssue = UserIssue::where('user_id', Auth::user()->id)
            ->where('issue_id', \intval($request->targetIssueId))->first();
        $myselfUserIssue->nickname = $myselfUserIssue->nickname()->first();
        $targetUserIssue = UserIssue::where('user_id', \intval($request->targetUserId))
            ->where('issue_id', \intval($request->targetIssueId))->first();

        $issue = Issue::find(\intval($request->targetIssueId));

        $firebase = new FirebaseLib(Constant::getFirebaseDefaultURL(), Constant::FIREBASE_DEFAULT_TOKEN);
        $messages = $firebase->get(Constant::FIREBASE_DEFAULT_PATH_MESSAGE.
                'user_'.Auth::user()->id.'/'.
                'issue_'.$request->targetIssueId.'/'.
                'user_'.$request->targetUserId.'/'
            );
        $messages = \json_decode($messages);

        if($messages != null){
            foreach($messages as $messageId => $message){
                if($messageId != 'has_new_message')  $message->created_at = Constant::getPassedTimeFromInt(\explode('_', $messageId)[0]);
            }

            $firebase->set(Constant::FIREBASE_DEFAULT_PATH_MESSAGE.
                'user_'.Auth::user()->id.'/'.
                'issue_'.$request->targetIssueId.'/'.
                'user_'.$request->targetUserId.'/'.
                'has_new_message'
                , false);
        }


        return response()->json(['status' => true,
            'value' => [
                'userIssue' => [
                    'issueTitle' => $issue->title,
                    'userInfo' => [
                        'name' => $myselfUserIssue->nickname->name,
                        'img' => $myselfUserIssue->nickname->img,
                        'color' => $myselfUserIssue->color,
                        'seq' => $myselfUserIssue->seq,
                        'status' => $myselfUserIssue->status
                    ]
                ],
                'messages' => $messages
            ]
        ]);
    }
}