<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Constant;

class UserIssue extends Model
{
    protected $table = 'user_issue';

    /**
     * 獲得當下議題參與使用者內容
     */
    public function getCurrent($issueId, $userId){
        $userIssue = $this::where('issue_id', $issueId)
            ->where('user_id', $userId)
            ->first();

        return $userIssue;
    }

    /**
     * 獲得當下議題參與人數
     */
    public function getUserNum($issueId){
        $pos = $this::where('issue_id', $issueId)
            ->where('status', Constant::USER_ISSUE_STATUS_POS)
            ->count();

        $neg = $this::where('issue_id', $issueId)
            ->where('status', Constant::USER_ISSUE_STATUS_NEG)
            ->count();

        return ['pos' => $pos, 'neg' => $neg];
    }

    /**
     * 檢查使用者是否參與當下議題
     */
    public function checkExist($issueId, $userId){
        $exist = $this::where('issue_id', $issueId)
            ->where('user_id', $userId)
            ->get();

        return (count($exist) > 0);
    }

    /**
     * 獲得當下議題的唯一辨認匿名
     */
    public function getUniqueNickname($issueId){
        $nicknameNum = \App\Nickname::count();
        $nicknameId = 0;
        $color = '#FFFFFF';
        do{
            $nicknameId = rand(1, $nicknameNum);
            $color = '#';
            for($i = 0; $i < 6; $i++) $color .= $this->rand0ToF();
        }while(
            count($this::where('issue_id', $issueId)
                ->where('nickname_id', $nicknameId)
                ->where('color', $color)
                ->get()) > 0
        );

        return [
            'nicknameId' => $nicknameId,
            'color' => $color,
            'seq' => $this->where('issue_id', $issueId)->max('seq') + 1
        ];
    }

    /**
     * 0 - 15 轉換 16bit
     */
    private function rand0ToF(){
        $rand = rand(0, 15);
        if($rand > 9){
            switch($rand){
                case 10:
                    $rand = 'A';
                    break;
                case 11:
                    $rand = 'B';
                    break;
                case 12:
                    $rand = 'C';
                    break;
                case 13:
                    $rand = 'D';
                    break;
                case 14:
                    $rand = 'E';
                    break;
                case 15:
                    $rand = 'F';
                    break;
            }
        }
        else $rand .= "";

        return $rand;
    }

    public function nickname(){
        return $this->belongsTo('App\Nickname');
    }

    public function issue(){
        return $this->belongsTo('\App\Issue');
    }
}
