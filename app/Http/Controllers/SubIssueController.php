<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Constant;
use \App\Issue;
use \App\UserIssue;
use \App\SubIssue;
use \App\UserSubIssue;
use \App\User;
use \Firebase\FirebaseLib;
use Auth;

class SubIssueController extends Controller
{
    public function getSubIssueDetail($subIssueId){
        $subIssue = SubIssue::findOrFail($subIssueId);
        $userIssue = new UserIssue();
        $userSubIssue = new UserSubIssue();
        $userInIssue = false;
        $userInSubIssue = false;
        if(Auth::check()){
            $userInIssue = $userIssue->checkExist($subIssue->issue_id, Auth::user()->id);
            $userInSubIssue = $userSubIssue->checkExist($subIssueId, Auth::user()->id);
            if($userInSubIssue){
                $user = $userIssue->getCurrent($subIssue->issue_id, Auth::user()->id);
                $user->status = $userSubIssue->getCurrent($subIssueId, Auth::user()->id)->status;
                // \setcookie(Constant::USER_CURRENT_ID, Auth::user()->id);
                // \Cookie::queue(Constant::USER_CURRENT_ID, Auth::user()->id, 10);
            }
        }
        // else \setcookie(Constant::USER_CURRENT_ID, Constant::WEBSOCKET_GUEST_USER_ID);
        $totalNum = $subIssue->getUserNum();

        //與 firebase 拿留言
        $bestCommentPos = ['message' => null, 'num' => 0];
        $bestCommentNeg = ['message' => null, 'num' => 0];
        $firebase = new FirebaseLib(Constant::getFirebaseDefaultURL(), Constant::FIREBASE_DEFAULT_TOKEN);
        $messages = $firebase->get(Constant::FIREBASE_DEFAULT_PATH_SUB_ISSUE_COMMENT.$subIssueId.'/');
        $messages = \json_decode($messages);
        if($messages !== null && count($messages) > 0){
            foreach($messages as $message){
                $people = User::find($message->user_id);
                $message->user = $people->userIssues->where('issue_id', $subIssue->issue_id)->first();

                //判斷最佳留言
                $thumbsTotal = 0;
                if(isset($message->thumb_up_pos)) $thumbsTotal = count((array)\json_decode($message->thumb_up_pos));
                if(isset($message->thumb_up_neg)) $thumbsTotal += count((array)\json_decode($message->thumb_up_neg));
                if($message->status == Constant::USER_ISSUE_STATUS_POS && $thumbsTotal > $bestCommentPos['num']){
                    $bestCommentPos['message'] = $message;
                    $bestCommentPos['num'] = $thumbsTotal;
                }
                else if($message->status == Constant::USER_ISSUE_STATUS_NEG && $thumbsTotal > $bestCommentNeg['num']){
                    $bestCommentNeg['message'] = $message;
                    $bestCommentNeg['num'] = $thumbsTotal;
                }
            }
        }

        $wait4Judges = new \stdClass();
        $hasJudges = false;
        if($userInSubIssue && $user->judge != Constant::USER_ISSUE_STATUS_NONE){
            //檢查是否有被檢舉的留言
            $judges = $firebase->get(Constant::FIREBASE_DEFAULT_PATH_SUB_ISSUE_JUDGE.$subIssueId.'/');
            $judges = \json_decode($judges);
            if($judges !== null && count($judges) > 0){
                foreach($judges as $messageId => $judgers){
                    foreach($judgers as $judgerId => $result){
                        if($judgerId == 'user_'.Auth::user()->id && $result == 'none'){
                            $wait4Judges->$messageId = true;
                            $hasJudges = true;
                            break;
                        }
                    }
                }
            }
        }

        \setcookie(Constant::SUB_ISSUE_CURRENT_ID, $subIssueId);
        \Cookie::queue(Constant::SUB_ISSUE_CURRENT_ID, $subIssueId, 10);

        //獲得母議題基本資訊
        $masterIssue = Issue::find($subIssue->issue_id);
        $masterIssueTotalNum = $userIssue->getUserNum($subIssue->issue_id);

        return view('issue.extend', [
                'subIssue' => $subIssue, 'userInSubIssue' => $userInSubIssue,
                'userInIssue' => $userInIssue, 'user' => (isset($user)) ? $user : null,
                'posNum' => $totalNum['pos'], 'negNum' => $totalNum['neg'],
                'bestCommentPos' => $bestCommentPos, 'bestCommentNeg' => $bestCommentNeg,
                'messages' => $messages,
                'masterIssue' => $masterIssue, 'masterIssueTotalNum' => $masterIssueTotalNum,
                'hasJudges' => $hasJudges, 'wait4Judges' =>$wait4Judges
            ]
        );
    }

    public function getJoinSubIssue(Request $request){
        if(!isset($request->id) || !isset($request->status)) {
            if(\Cookie::get(Constant::SUB_ISSUE_CURRENT_ID) !== null){
                return redirect()->route('issue.extend', ['id' => \Cookie::get(Constant::SUB_ISSUE_CURRENT_ID)]);
            }
            return redirect()->route('issue.index');
        }
        $subIssue = SubIssue::find($request->id);
        $status = $request->status;
        if(Auth::check()){
            $userIssue = new UserIssue();
            if(!$userIssue->checkExist($subIssue->issue_id, Auth::user()->id)){
                return redirect()->route('issue.detail', ['id' => $subIssue->issue_id]);
            }
            else{
                $userSubIssue = new UserSubIssue();
                if(!$userSubIssue->checkExist($subIssue->id, Auth::user()->id)){
                    $userSubIssue->issue_id = $subIssue->issue_id;
                    $userSubIssue->user_id = Auth::user()->id;
                    $userSubIssue->status = $status;
                    $subIssue->userSubIssues()->save($userSubIssue);
                }
                else{
                    $userSubIssue = $userSubIssue->getCurrent($subIssue->id, Auth::user()->id);
                    $userSubIssue->status = $status;
                    $userSubIssue->save();
                }

                if(strcmp(config('app.env'), 'local') != 0){
                    $userIssue = new UserIssue();
                    $totalNum = $userSubIssue->getUserNum();
                    $posPercent = round($totalNum['pos'] / ($totalNum['pos'] + $totalNum['neg']) * 100);
                    \exec('/usr/local/bin/node /var/www/html/pie-chart-maker/app.js '.
                        $request->id.' 1 '.$posPercent, $output, $return_var);
                }
            }
        }
        return redirect()->route('issue.extend', ['id' => $subIssue->id]);
    }
}
