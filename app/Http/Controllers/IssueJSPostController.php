<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Constant;
use \App\Issue;
use \App\UserIssue;
use \App\User;
use \Firebase\FirebaseLib;
use Auth;
use Mail;
use \App\Mail\CommentTag;

class IssueJSPostController extends Controller
{
    public function postThumbChange(Request $request){
        $checkUserInIssue = UserIssue::where('user_id', $request->userId)
            ->where('issue_id', $request->issueId)->get();
        if(count($checkUserInIssue) < 1) return response()->json(['status' => false]);

        $firebase = new FirebaseLib(Constant::getFirebaseDefaultURL(), Constant::FIREBASE_DEFAULT_TOKEN);
        $targetComment = $firebase->get(Constant::FIREBASE_DEFAULT_PATH_ISSUE_COMMENT.
                $request->issueId.'/'.$request->messageId.'/');
        $targetComment = \json_decode($targetComment);

        $userId = $request->userId;
        $userStatus = Constant::USER_ISSUE_STATUS_POS;

        if($request->up == 'up'){
            $userIssue = UserIssue::where('user_id', $userId)
                ->where('issue_id', $request->issueId)->first();
            if($userIssue->status == Constant::USER_ISSUE_STATUS_POS){
                $currentThumbs = new \stdClass();
                if(isset($targetComment->thumb_up_pos)){
                    $currentThumbs = \json_decode($targetComment->thumb_up_pos);
                }
                $currentThumbs->$userId = true;
                $firebase->set(Constant::FIREBASE_DEFAULT_PATH_ISSUE_COMMENT.
                    $request->issueId.'/'.$request->messageId.'/thumb_up_pos/'
                    , \json_encode($currentThumbs));
            }
            else{
                $currentThumbs = new \stdClass();
                if(isset($targetComment->thumb_up_neg)){
                    $currentThumbs = \json_decode($targetComment->thumb_up_neg);
                }
                $currentThumbs->$userId = true;
                $firebase->set(Constant::FIREBASE_DEFAULT_PATH_ISSUE_COMMENT.
                    $request->issueId.'/'.$request->messageId.'/thumb_up_neg/'
                    , \json_encode($currentThumbs));
            }
            $userStatus = $userIssue->status;
        }
        else{
            if(isset($targetComment->thumb_up_pos)){
                $thumbs = \json_decode($targetComment->thumb_up_pos);
                if(isset($thumbs->$userId)) {
                    unset($thumbs->$userId);
                    $firebase->set(Constant::FIREBASE_DEFAULT_PATH_ISSUE_COMMENT.
                        $request->issueId.'/'.$request->messageId.'/thumb_up_pos/'
                        , \json_encode($thumbs));
                    $userStatus = Constant::USER_ISSUE_STATUS_POS;
                }
            }
            if(isset($targetComment->thumb_up_neg)){
                $thumbs = \json_decode($targetComment->thumb_up_neg);
                if(isset($thumbs->$userId)) {
                    unset($thumbs->$userId);
                    $firebase->set(Constant::FIREBASE_DEFAULT_PATH_ISSUE_COMMENT.
                        $request->issueId.'/'.$request->messageId.'/thumb_up_neg/'
                        , \json_encode($thumbs));
                    $userStatus = Constant::USER_ISSUE_STATUS_NEG;
                }
            }
        }

        return response()->json(['status' => true, 'userStatus' => $userStatus]);
    }

    public function postDeleteComment(Request $request){
        if(!isset(explode('_', $request->messageId)[1]) || Auth::user()->id != explode('_', $request->messageId)[1])
            return response()->json(['status' => false]);

        $firebase = new FirebaseLib(Constant::getFirebaseDefaultURL(), Constant::FIREBASE_DEFAULT_TOKEN);
        $firebase->set(Constant::FIREBASE_DEFAULT_PATH_ISSUE_COMMENT.
                    $request->issueId.'/'.$request->messageId.'/delete/'
                    , true);

        // $firebase->delete(Constant::FIREBASE_DEFAULT_PATH_ISSUE_COMMENT.
        //     $request->issueId.'/'.$request->messageId.'/');

        return response()->json(['status' => true]);
    }

    public function postEditComment(Request $request){
        if(!isset(explode('_', $request->messageId)[1]) || Auth::user()->id != explode('_', $request->messageId)[1])
            return response()->json(['status' => false]);

        $firebase = new FirebaseLib(Constant::getFirebaseDefaultURL(), Constant::FIREBASE_DEFAULT_TOKEN);
        $firebase->set(Constant::FIREBASE_DEFAULT_PATH_ISSUE_COMMENT.
            $request->issueId.'/'.$request->messageId.'/comment/'
            , $request->content);

        return response()->json(['status' => true]);
    }

    public function postJudge(Request $request){
        $judges = new \stdClass();
        $posJudge = null;
        $negJudge = null;
        $midJudge = null;

        $posJudges = UserIssue::where('issue_id', $request->issueId)
            ->where('judge', Constant::USER_ISSUE_STATUS_POS)
            ->get();

        $negJudges = UserIssue::where('issue_id', $request->issueId)
            ->where('judge', Constant::USER_ISSUE_STATUS_NEG)
            ->get();

        if(count($posJudges) > 0){
            $rand = rand(0, count($posJudges) - 1);
            $posJudge = 'user_'.$posJudges[$rand]->user_id;
            $judges->$posJudge = 'none';
        }
        if(count($negJudges) > 0){
            $rand = rand(0, count($negJudges) - 1);
            $negJudge = 'user_'.$negJudges[$rand]->user_id;
            $judges->$negJudge = 'none';
        }
        if((count($posJudges) + count($negJudges) > 0)){
            $rand = rand(0, count($posJudges) + count($negJudges) - 1);
            if($rand < count($posJudges)) $midJudge = 'user_'.$posJudges[$rand]->user_id;
            else $midJudge = 'user_'.$negJudges[$rand - count($posJudges)]->user_id;
            if($midJudge == $posJudge || $midJudge == $negJudge) $midJudge = null;
            else $judges->$midJudge = 'none';
        }

        if(isset($judges)){
            $firebase = new FirebaseLib(Constant::getFirebaseDefaultURL(), Constant::FIREBASE_DEFAULT_TOKEN);
            $firebase->set(Constant::FIREBASE_DEFAULT_PATH_ISSUE_JUDGE.
                    $request->issueId.'/'.
                    $request->messageId
                , $judges);
        }


        return response()->json(['status' => true, 'data' => $judges]);
    }

    public function postJudgeReply(Request $request){
        $firebase = new FirebaseLib(Constant::getFirebaseDefaultURL(), Constant::FIREBASE_DEFAULT_TOKEN);
        $targetComment = $firebase->get(Constant::FIREBASE_DEFAULT_PATH_ISSUE_JUDGE.
                $request->issueId.'/'.$request->messageId.'/');
        $targetComment = \json_decode($targetComment);

        $resultValue = 'none';

        $resultDelete = 0;
        $resultKeep = 0;
        foreach($targetComment as $userId => $reply){
            if($userId == 'user_'.$request->userId){
                $firebase->set(Constant::FIREBASE_DEFAULT_PATH_ISSUE_JUDGE.
                    $request->issueId.'/'.$request->messageId.'/'.$userId
                , $request->keep);
                if($request->keep == 'keep') $resultKeep++;
                else $resultDelete++;
            }
            else if($reply != 'none'){
                if($reply == 'keep') $resultKeep++;
                else $resultDelete++;
            }
        }

        $totalJudges = count((array)$targetComment);
        switch($totalJudges){
            case 3:
                $gap = 2;
                break;
            default:
                $gap = 1;
                break;
        }
        if($resultDelete >= $gap){
            //刪檢舉區
            $firebase->delete(Constant::FIREBASE_DEFAULT_PATH_ISSUE_JUDGE.
                $request->issueId.'/'.$request->messageId.'/');
            //刪留言
            $firebase->set(Constant::FIREBASE_DEFAULT_PATH_ISSUE_COMMENT.
                    $request->issueId.'/'.$request->messageId.'/delete/'
                    , true);
            // $firebase->delete(Constant::FIREBASE_DEFAULT_PATH_ISSUE_COMMENT.
            //     $request->issueId.'/'.$request->messageId.'/');
            $resultValue = 'delete';
        }
        else if($resultKeep >= $gap){
            //刪檢舉區
            $firebase->delete(Constant::FIREBASE_DEFAULT_PATH_ISSUE_JUDGE.
                $request->issueId.'/'.$request->messageId.'/');
            $resultValue = 'keep';
        }

        return response()->json(['status' => true, 'value' => $resultValue]);
    }

    public function postTagFloor(Request $request){
        //如果有標記特定樓層的話就要寄信
        if(strcmp(config('app.env'), 'local') == 0){
            return response()->json(['status' => true]);
        }
        else{
            $taggedUser = User::find($request->userId);
            Mail::to($taggedUser->email)->send(new CommentTag($request->issueTitle, $request->floor, $request->issueUrl));
            // Mail::to("demo.or.test.0823@gmail.com")->send(new CommentTag($request->issueTitle, $request->floor, $request->issueUrl));
            return response()->json(['status' => true]);
        }
    }
}
