<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\User;
use Auth;
use Session;
use \App\UserIssue;
use \App\UserSubIssue;
use \App\Issue;
use \App\SubIssue;
use \App\Constant;
use \Firebase\FirebaseLib;

class UserController extends Controller
{
    /**
     * 跳轉至登入|註冊
     */
    public function getSignInUp()
    {
    	return view('user.signinup');
    }

    public function postCheckFB(Request $request){
        return response()->json(['status' => (count(User::where('fb_id', (string)$request->id)->get()) > 0)]);
    }

    /**
     * 利用FB做註冊
     */
    public function postSignUpFB(Request $request){
        $this->validate($request, [
            'fbId' => 'required|unique:users,fb_id',
            'fbEmail' => 'email|required',
            'fbName'=> 'required'
        ]);

        if(count(User::where('email', $request->fbEmail)->get()) > 0){
            $user = User::where('email', $request->fbEmail)->first();
            $user->fb_id = $request->fbId;
            $user->save();
        }
        else{
            $user = new User([
                'username' => $request->fbEmail,
                'pwd' => bcrypt(bcrypt($request->fbId)),
                'fb_id' => (string)$request->fbId,
                'email' => $request->fbEmail,
                'nickname' => $request->fbName,
                'groups' => 'user'
            ]);
            $user->save();
        }

        // Auth::login($user);

        // if(Session::has('oldUrl')){
        //     $oldUrl = Session::get('oldUrl');
        //     Session::forget('oldUrl');
        //     return redirect()->to($oldUrl);
        // }

        // return redirect()->route('issue.index');
        return response()->json(['status' => true]);
    }

    /**
     * 普通註冊
     */
    public function postSignUp(Request $request)
    {

    	$this->validate($request, [
    		'signup_account' => 'email|required|unique:users,username',
            'signup_password' => 'required|alpha_num|min:4',
            'signup_name'=> 'required'
    	]);

    	$user = new User([
            'username' => $request->input('signup_account'),
            'pwd' => bcrypt($request->input('signup_password')),
            'fb_id' => null,
            'email' => $request->input('signup_account'),
            'nickname' => $request->input('signup_name'),
            'groups' => 'user'
    	]);
        $user->save();

        Auth::login($user);

        if(Session::has('oldUrl')){
            $oldUrl = Session::get('oldUrl');
            Session::forget('oldUrl');
            return redirect()->to($oldUrl);
        }

    	return redirect()->route('issue.index');
    }

    /**
     * 嘗試FB登入
     */
    public function postSignInFB(Request $request){
        $this->validate($request, [
            'fbId' => 'required'
        ]);

        if(count(User::where('fb_id', $request->fbId)->get()) > 0){
            $user = User::where('fb_id', $request->fbId)->first();
            Auth::login($user);

            if(Session::has('oldUrl')){
                $oldUrl = Session::get('oldUrl');
                Session::forget('oldUrl');
                return redirect()->to($oldUrl);
            }
            return redirect()->route('issue.index');
        }

        // if (Auth::attempt([
        //         'fb_id' => $request->fbId
        //         ])) {
        //     if(Session::has('oldUrl')){
        //         $oldUrl = Session::get('oldUrl');
        //         Session::forget('oldUrl');
        //         return redirect()->to($oldUrl);
        //     }
        //     return redirect()->route('issue.index');
        // }
        return redirect()->back();
    }

    /**
     * 嘗試普通登入
     */
    public function postSignIn(Request $request)
    {
    	$this->validate($request, [
    		'signin_account' => 'email|required',
            'signin_password' => 'required|alpha_num|min:4',
    	]);

        if (Auth::attempt([
                'username' => $request->input('signin_account'),
                'password' => $request->input('signin_password')
                ])) {
            if(Session::has('oldUrl')){
                $oldUrl = Session::get('oldUrl');
                Session::forget('oldUrl');
                return redirect()->to($oldUrl);
            }
            return redirect()->route('issue.index');
        }
        return redirect()->back();
    }

    /**
     * 進入使用者資訊介面
     */
    public function postProfile(Request $request)
    {
        $targetId = $request->targetId;
        // $targetId = 2;
        // $request->id = 1;

        $myself = false;
        $myselfInIssue = false;
        $title;
        $user = Auth::user();
        if($targetId == Auth::user()->id){
            $user = Auth::user();
            $myself = true;
            $title = $user->nickname;
        }
        else {
            $user = User::find($targetId);
            $userInIssue = $user->userIssues()->where('issue_id', $request->id)->first();
            $title = new \stdClass();
            $title->img = $userInIssue->nickname->img;
            $title->name = $userInIssue->nickname->name;
            $title->color = $userInIssue->color;
            $title->seq = $userInIssue->seq;

            $myselfIssue = UserIssue::where('user_id', Auth::user()->id)->where('issue_id', $request->id)->first();
            if($myselfIssue != null) $myselfInIssue = true;
        }

        $firebase = new FirebaseLib(Constant::getFirebaseDefaultURL(), Constant::FIREBASE_DEFAULT_TOKEN);

        $issues = array();
        $userIssues = $user->userIssues();
        foreach($userIssues->get() as $userIssue){
            $issue = Issue::find($userIssue->issue_id);
            $total = $userIssue->getUserNum($issue->id);
            $totalNum = (($total['pos'] + $total['neg']) != 0) ? ($total['pos'] + $total['neg']) : 1;
            $issue->posPercent = round($total['pos'] / $totalNum * 100);
            $issue->negPercent = round($total['neg'] / $totalNum * 100);
            $issue->status = $userIssue->status;
            $issue->isCommentPublic = $userIssue->is_comment_public;
            $issue->isExtend = false;

            if($myself || $issue->isCommentPublic){
                $messages = $firebase->get(Constant::FIREBASE_DEFAULT_PATH_ISSUE_COMMENT.$issue->id.'/');
                $messages = \json_decode($messages);
                if($messages !== null && count($messages) > 0){
                    foreach($messages as $message){
                        if($message->user_id == $targetId){
                            $people = User::find($message->user_id);
                            $message->user = $people->userIssues->where('issue_id', $issue->id)->first();
                        }
                    }
                    $issue->messages = $messages;
                }
            }

            array_push($issues, $issue);
        }

        $userSubIssues = $user->userSubIssues()->get();
        foreach($userSubIssues as $userSubIssue){
            $subIssue = SubIssue::find($userSubIssue->sub_issue_id);
            $total = $subIssue->getUserNum();
            $totalNum = (($total['pos'] + $total['neg']) != 0) ? ($total['pos'] + $total['neg']) : 1;
            $subIssue->posPercent = round($total['pos'] / $totalNum) * 100;
            $subIssue->negPercent = round($total['neg'] / $totalNum) * 100;
            $subIssue->status = $userSubIssue->status;
            $subIssue->isCommentPublic = $userSubIssue->is_comment_public;
            $subIssue->isExtend = true;

            if($myself || $subIssue->isCommentPublic){
                $messages = $firebase->get(Constant::FIREBASE_DEFAULT_PATH_SUB_ISSUE_COMMENT.$subIssue->id.'/');
                $messages = \json_decode($messages);
                if($messages !== null && count($messages) > 0){
                    foreach($messages as $message){
                        if($message->user_id == $targetId){
                            $people = User::find($message->user_id);
                            $message->user = $people->userIssues->where('issue_id', $subIssue->issue_id)->first();
                        }
                    }
                    $subIssue->messages = $messages;
                }
            }

            array_push($issues, $subIssue);
        }

        return view('user.profile', ['myself' => $myself, 'myselfInIssue' => $myselfInIssue,
            'userIssueId' => isset($userInIssue) ? $userInIssue->id : null,
            'title' => $title, 'issues' => $issues]);
    }

    /**
     * 更改議題立場
     */
    public function postChangeStatus(Request $request){
        $user = Auth::user();

        $userIssue;
        $totalNum;
        if($request->isIssue == 1){
            $userIssue = UserIssue::where('issue_id', $request->id)
                ->where('user_id', Auth::user()->id)->first();
            $totalNum = $userIssue->getUserNum($request->id);
            if(!$userIssue->judge == Constant::USER_ISSUE_STATUS_NONE)
                $userIssue->judge = $request->targetStatus;
        }
        else{
            $userIssue = UserSubIssue::where('sub_issue_id', $request->id)
                ->where('user_id', Auth::user()->id)->first();
            $totalNum = $userIssue->getUserNum();
        }
        $userIssue->status = $request->targetStatus;
        $userIssue->save();

        if($request->targetStatus == Constant::USER_ISSUE_STATUS_POS) {
            $totalNum['pos']++;
            $totalNum['neg']--;
        }
        else {
            $totalNum['pos']--;
            $totalNum['neg']++;
        }

        $total = $totalNum['pos'] + $totalNum['neg'];
        $posPercent = round($totalNum['pos'] / $total * 100);
        $negPercent = round($totalNum['neg'] / $total * 100);
        if(strcmp(config('app.env'), 'local') != 0){
            \exec('/usr/local/bin/node /var/www/html/pie-chart-maker/app.js '.
            $request->id.' '.(($request->isIssue == 1) ? 0 : 1).' '.$posPercent, $output, $return_var);
        }

        return response()->json(['status' => true, 'pos' => $posPercent.'%', 'neg' => $negPercent.'%']);
    }

    /**
     * 更改議題發言隱私
     */
    public function postChangePublic(Request $request){
        $user = Auth::user();

        $userIssue;
        if($request->isIssue == 1){
            $userIssue = UserIssue::where('issue_id', $request->id)
                ->where('user_id', Auth::user()->id)->first();
        }
        else{
            $userIssue = UserSubIssue::where('sub_issue_id', $request->id)
                ->where('user_id', Auth::user()->id)->first();
        }
        if($userIssue->is_comment_public) $userIssue->is_comment_public = false;
        else $userIssue->is_comment_public = true;
        $userIssue->save();

        return response()->json(['status' => true]);
    }

    /**
     * 登出
     */
    public function getLogout()
    {
        Auth::logout();
        setcookie (Constant::USER_CURRENT_ID, "", time() - 3600);
        return redirect()->route('issue.index');
        // return redirect()->back();
    }

    public function postUserId(){
        if(Auth::check()){
            return response()->json(['value' => \base64_encode(Auth::user()->id)]);
        }
        else{
            return response()->json(['value' => \base64_encode(Constant::WEBSOCKET_GUEST_USER_ID)]);
        }
    }
}
