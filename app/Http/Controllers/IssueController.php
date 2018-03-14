<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Constant;
use \App\Issue;
use \App\SubIssue;
use \App\UserIssue;
use \App\User;
use \Firebase\FirebaseLib;
use \App\Mail\CommentTag;
use Auth;
use Mail;

class IssueController extends Controller
{
    public function getIndex(){
        $issues = Issue::all();

        return view('issue.index', ['issues' => $issues]);
    }

    /**
     * 進入議題內容
     * Cookie issue 作為登入後重新導入依據
     * Cookie user 作為 websocket 溝通判斷
     * Cookie 用PHP原生的跟Laravel的同步，不然JS抓不到
     */
    public function getIssueDetail($id){
        $userIssue = new UserIssue();
        $issue = Issue::findOrFail($id);
        $userInIssue = false;
        if(Auth::check()){
            $userInIssue = $userIssue->checkExist($id, Auth::user()->id);
            if($userInIssue){
                $user = $userIssue->getCurrent($id, Auth::user()->id);
                // \setcookie(Constant::USER_CURRENT_ID, Auth::user()->id);
                // \Cookie::queue(Constant::USER_CURRENT_ID, Auth::user()->id, 10);
            }
        }
        // else \setcookie(Constant::USER_CURRENT_ID, Constant::WEBSOCKET_GUEST_USER_ID);
        $totalNum = $userIssue->getUserNum($id);

        //與 firebase 拿留言
        $bestCommentPos = ['message' => null, 'num' => 0];
        $bestCommentNeg = ['message' => null, 'num' => 0];
        $firebase = new FirebaseLib(Constant::getFirebaseDefaultURL(), Constant::FIREBASE_DEFAULT_TOKEN);
        $messages = $firebase->get(Constant::FIREBASE_DEFAULT_PATH_ISSUE_COMMENT.$id.'/');
        $messages = \json_decode($messages);
        if($messages !== null && count($messages) > 0){
            foreach($messages as $message){
                $people = User::find($message->user_id);
                $message->user = $people->userIssues->where('issue_id', $id)->first();

                //判斷最佳留言
                if(!isset($message->delete)){
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
        }

        $wait4Judges = new \stdClass();
        $hasJudges = false;
        if($userInIssue && $user->judge != Constant::USER_ISSUE_STATUS_NONE){
            //檢查是否有被檢舉的留言
            $judges = $firebase->get(Constant::FIREBASE_DEFAULT_PATH_ISSUE_JUDGE.$id.'/');
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


        //找尋延伸議題
        $now = new \DateTime('now');
        $voting = $issue->subIssues()->where('deadline', '>', $now->getTimestamp())->first();
        $hasVote = ($voting != null);
        $subIssues = $issue->subIssues()->get();
        foreach($subIssues as $subIssue){
            $subIssueUsers = $subIssue->getUserNum();
            $subIssue->posNum = $subIssueUsers['pos'];
            $subIssue->negNum = $subIssueUsers['neg'];
        }

        \setcookie(Constant::ISSUE_CURRENT_ID, $id);
        \Cookie::queue(Constant::ISSUE_CURRENT_ID, $id, 10);

        return view('issue.detail', [
                'issue' => $issue, 'userInIssue' => $userInIssue,
                'user' => (isset($user)) ? $user : null,
                'posNum' => $totalNum['pos'], 'negNum' => $totalNum['neg'],
                'bestCommentPos' => $bestCommentPos, 'bestCommentNeg' => $bestCommentNeg,
                'messages' => $messages,
                'subIssues' => $subIssues, 'hasVote' => $hasVote, 'voting' => $voting,
                'hasJudges' => $hasJudges, 'wait4Judges' =>$wait4Judges
            ]
        );
    }

    public function getCreateIssue(Request $request){
        if(isset($request->id)){
            $issue = null;
            $isExtend = false;
            switch($request->issueType){
                case 'issue':
                    $issue = Issue::find($request->id);
                    break;
                case 'extend':
                    $issue = SubIssue::find($request->id);
                    $isExtend = true;
                    break;
            }
            return view('issue.create', ['issue' => $issue, 'isExtend' => $isExtend]);
        }

        return view('issue.create');
    }

    public function postCreateIssue(Request $request){
        $this->validate($request, [
            'title' => 'required|unique:issues',
            'description' => 'required',
            'checkCycle'=> 'required',
            'voteCycle'=> 'required'
        ]);

        $issue = new Issue();
        $issue->title = $request->input('title');
        $issue->description = $request->input('description');
        $issue->check_cycle = $request->input('checkCycle');
        $issue->vote_cycle = $request->input('voteCycle');
        $check_point = new \DateTime('now');
        $check_point->modify('+'.$request->input('checkCycle').' day');
        $issue->check_point = strtotime($check_point->format('Y-m-d H:i:s'));

        Auth::user()->issues()->save($issue);

        $this->joinAnIssue($issue->id, Auth::user()->id, Constant::USER_ISSUE_STATUS_POS);

        if(strcmp(config('app.env'), 'local') != 0){
            $userIssue = new UserIssue();
            $totalNum = $userIssue->getUserNum($issue->id);
            $posPercent = round($totalNum['pos'] / ($totalNum['pos'] + $totalNum['neg']) * 100);
            \exec('/usr/local/bin/node /var/www/html/pie-chart-maker/app.js '.
                $issue->id.' 0 '.$posPercent, $output, $return_var);
        }

        return redirect()->route('issue.index');
    }

    public function postEditIssue(Request $request){
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required'
        ]);

        $issue = null;
        if($request->issueType == 'issue'){
            $issue = Issue::find($request->issueId);
            $issue->check_cycle = $request->input('checkCycle');
            $issue->vote_cycle = $request->input('voteCycle');

            $check_point = new \DateTime('now');
            $check_point->modify('+'.$request->input('checkCycle').' day');
            $issue->check_point = strtotime($check_point->format('Y-m-d H:i:s'));
        }
        else{
            $issue = SubIssue::find($request->issueId);
        }

        $issue->title = $request->input('title');
        $issue->description = $request->input('description');

        $issue->save();

        if($request->issueType == 'issue'){
            return redirect()->route('issue.detail', ['id' => $request->issueId]);
        }
        else{
            return redirect()->route('issue.extend', ['id' => $request->issueId]);
        }
    }

    public function getJoinIssue(Request $request){
        if(!isset($request->id) || !isset($request->status)) {
            if(\Cookie::get(Constant::ISSUE_CURRENT_ID) !== null){
                return redirect()->route('issue.detail', ['id' => \Cookie::get(Constant::ISSUE_CURRENT_ID)]);
            }
            return redirect()->route('issue.index');
        }
        $issueId = $request->id;
        $status = $request->status;
        if(Auth::check()){
            $userIssue = new UserIssue();
            if(!$userIssue->checkExist($issueId, Auth::user()->id)){
                $this->joinAnIssue($issueId, Auth::user()->id, $status);
            }
            else{
                $userIssue = $userIssue->getCurrent($issueId, Auth::user()->id);
                $userIssue->status = $status;
                if(!$userIssue->judge == Constant::USER_ISSUE_STATUS_NONE)
                    $userIssue->judge = $status;
                $userIssue->save();
            }

            if(strcmp(config('app.env'), 'local') != 0){
                $userIssue = new UserIssue();
                $totalNum = $userIssue->getUserNum($issueId);
                $posPercent = round($totalNum['pos'] / ($totalNum['pos'] + $totalNum['neg']) * 100);
                \exec('/usr/local/bin/node /var/www/html/pie-chart-maker/app.js '.
                    $issueId.' 0 '.$posPercent, $output, $return_var);
            }
        }
        return redirect()->route('issue.detail', ['id' => $issueId]);
    }

    public function getBecomeJudge(Request $request){
        if(!isset($request->id) || !isset($request->be)) {
            if(\Cookie::get(Constant::ISSUE_CURRENT_ID) !== null){
                return redirect()->route('issue.detail', ['id' => \Cookie::get(Constant::ISSUE_CURRENT_ID)]);
            }
            return redirect()->route('issue.index');
        }
        $issueId = $request->id;
        $be = $request->be;
        if(Auth::check()){
            $userIssue = new UserIssue();
            if($userIssue->checkExist($issueId, Auth::user()->id)){
                $userIssue = $userIssue->getCurrent($issueId, Auth::user()->id);
                $userIssue->judge = ($be) ? $userIssue->status : Constant::USER_ISSUE_STATUS_NONE;
                $userIssue->save();
            }
        }
        return redirect()->route('issue.detail', ['id' => $issueId]);
    }

    private function joinAnIssue($issueId, $userId, $status){
        $userIssue = new UserIssue();
        $userIssue->issue_id = $issueId;
        $userIssue->user_id = $userId;
        $unique = $userIssue->getUniqueNickname($issueId);
        $userIssue->nickname_id = $unique['nicknameId'];
        $userIssue->color = $unique['color'];
        $userIssue->seq = $unique['seq'];
        $userIssue->status = $status;
        $userIssue->judge = 0;

        $userIssue->save();
    }
}
