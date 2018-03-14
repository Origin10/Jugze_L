<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubIssueVote extends Model
{
    protected $table = 'sub_issue_vote';

    public function subIssue(){
        return $this->belongsTo('\App\SubIssue');
    }

    public function subIssueVoters(){
        return $this->hasMany('\App\SubIssueVoter');
    }
}
