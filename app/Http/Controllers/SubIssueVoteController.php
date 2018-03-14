<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Issue;
use \App\SubIssue;
use \App\SubIssueVote;
use \App\SubIssueVoter;
use \Auth;

class SubIssueVoteController extends Controller
{
    public function getSubIssueVote($id){
        $subIssueId = $id;
        $subIssue = SubIssue::find($subIssueId);
        $masterIssueTitle = $subIssue->issue()->select('title')->first()->title;

        $options = $subIssue->subIssueVotes()->get();
        $alreadyPost = (($subIssue->subIssueVotes()->where('user_id', Auth::user()->id)->first()) != null);

        $alreadyVote = [];
        foreach($options as $option){
            $option->voters = $option->subIssueVoters()->get();
            if($option->voters->where('user_id', Auth::user()->id)->first() != null) 
                $alreadyVote = array_merge($alreadyVote, ['id'.$option->id => true]);
        }

        return view('issue/vote', [
            'subIssue' => $subIssue,
            'title' => $masterIssueTitle,
            'options' => $options,
            'alreadyPost' => $alreadyPost,
            'alreadyVote' => $alreadyVote
        ]);
    }

    public function postCreateSubIssueVote(Request $request){
        $subIssueId = $request->id;
        $targetTitle = trim($request->title);
        //檢查是否標題沒有
        if($targetTitle == "") return back()->withErrors(['msg' => ['標題不得為空，亦不得重複']]);
        $subIssueVoteTitles = SubIssue::find($subIssueId)->subIssueVotes()->select('title')->get();
        foreach($subIssueVoteTitles as $subIssueVoteTitle){
            if($subIssueVoteTitle->title == $targetTitle)
                return back()->withErrors(['msg' => ['標題不得為空，亦不得重複']]);
        }

        $subIssue = SubIssue::find($subIssueId);

        $newSubIssueVote = new SubIssueVote();
        $newSubIssueVote->issue_id = $subIssue->issue()->first()->id;
        $newSubIssueVote->user_id = Auth::user()->id;
        $newSubIssueVote->title = $targetTitle;
        SubIssue::find($subIssueId)->subIssueVotes()->save($newSubIssueVote);

        return redirect()->route('issue.subIssueVote', ['id' => $subIssueId]);
    }

    public function postVoteSubIssue(Request $request){
        $optionId = $request->optionId;
        $subIssueVote = SubIssueVote::find($optionId);
        if($request->value == 'true'){
            $newVoter = new SubIssueVoter();
            $newVoter->user_id = Auth::user()->id;
            $newVoter->issue_id = $subIssueVote->subIssue()->first()->issue_id;
            $newVoter->sub_issue_id = $subIssueVote->subIssue()->first()->id;
            $subIssueVote->subIssueVoters()->save($newVoter);
            return response()->json(['status' => true]);
        }
        else{
            $existVoter = $subIssueVote->subIssueVoters()->where('user_id', Auth::user()->id);
            $existVoter->delete();
            return response()->json(['status' => true]);
        }
    }
}
