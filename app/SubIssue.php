<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubIssue extends Model
{
    public function issue(){
        return $this->belongsTo('App\Issue');
    }

    public function subIssueVotes(){
        return $this->hasMany('App\SubIssueVote');
    }
    public function userSubIssues(){
        return $this->hasMany('App\UserSubIssue');
    }

    /**
     * 獲得當下議題參與人數
     */
    public function getUserNum(){
        $pos = $this->hasMany('App\UserSubIssue')
            ->where('status', Constant::USER_ISSUE_STATUS_POS)
            ->count();

        $neg = $this->hasMany('App\UserSubIssue')
            ->where('status', Constant::USER_ISSUE_STATUS_NEG)
            ->count();

        return ['pos' => $pos, 'neg' => $neg];
    }
}
