<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSubIssue extends Model
{
    protected $table = 'user_sub_issue';

    /**
     * 檢查使用者是否參與當下議題
     */
    public function checkExist($subIssueId, $userId){
        $exist = $this::where('sub_issue_id', $subIssueId)
            ->where('user_id', $userId)
            ->get();

        return (count($exist) > 0);
    }

    /**
     * 獲得當下議題參與使用者內容
     */
    public function getCurrent($subIssueId, $userId){
        return $this::where('sub_issue_id', $subIssueId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * 獲得當下議題參與人數
     */
    public function getUserNum(){
        $pos = $this::where('status', Constant::USER_ISSUE_STATUS_POS)
            ->count();

        $neg = $this::where('status', Constant::USER_ISSUE_STATUS_NEG)
            ->count();

        return ['pos' => $pos, 'neg' => $neg];
    }

    public function subIssue(){
        return $this->belongsTo('\App\subIssue');
    }
}
