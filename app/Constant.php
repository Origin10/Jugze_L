<?php
namespace App;

class Constant
{
    const USER_ISSUE_STATUS_NONE = 0;
    const USER_ISSUE_STATUS_POS = 1;
    const USER_ISSUE_STATUS_NEG = 2;

    const ISSUE_CURRENT_ID = "cookie_issue_id";
    const USER_CURRENT_ID = "cookie_user_id";
    const SUB_ISSUE_CURRENT_ID = "cookie_sub_issue_id";

    // const FIREBASE_DEFAULT_URL = 'https://jugze-local.firebaseio.com/';
    const FIREBASE_DEFAULT_TOKEN = '';
    const FIREBASE_DEFAULT_PATH = '/';
    const FIREBASE_DEFAULT_PATH_ISSUE_COMMENT = '/issue_comment/';
    const FIREBASE_DEFAULT_PATH_SUB_ISSUE_COMMENT = '/sub_issue_comment/';
    // const FIREBASE_DEFAULT_PATH_USER_NOTIFICATION = '/user_notification/';
    const FIREBASE_DEFAULT_PATH_ISSUE_JUDGE = '/issue_judge/';
    const FIREBASE_DEFAULT_PATH_SUB_ISSUE_JUDGE = '/sub_issue_judge/';
    const FIREBASE_DEFAULT_PATH_MESSAGE = '/message/';

    const WEBSOCKET_GUEST_USER_ID = -1;
    const WEBSOCKET_TYPE_FIRST_MEET = "firstMeet";

    const WEBSOCKET_TYPE_COMMENT_ISSUE = "comment_issue";
    const WEBSOCKET_TYPE_COMMENT_SUB_ISSUE = "comment_sub_issue";
    const WEBSOCKET_TYPE_MESSAGE = 'message';

    static function getFirebaseDefaultURL(){
        // return getenv('FIREBASE_URL');
        return (strcmp(config('app.env'), 'local') == 0) ? 'https://jugze-local.firebaseio.com/' : 'https://jugze-ox.firebaseio.com/';
    }

    static function getFirebaseDefaultPath($type){
        switch($type){
            case self::WEBSOCKET_TYPE_COMMENT_ISSUE:
                return self::FIREBASE_DEFAULT_PATH_ISSUE_COMMENT;
                break;
            case self::WEBSOCKET_TYPE_COMMENT_SUB_ISSUE:
                return self::FIREBASE_DEFAULT_PATH_SUB_ISSUE_COMMENT;
                break;
            case self::WEBSOCKET_TYPE_MESSAGE:
                return self::FIREBASE_DEFAULT_PATH_MESSAGE;
                break;
        }
    }

    static function getCurrentTime(){
        date_default_timezone_set('Asia/Taipei');
        $now = new \DateTime('now');
        return date('Y-m-d H:i:s', $now->getTimestamp());
    }

    static function getTimeFromInt($timestamp){
        date_default_timezone_set('Asia/Taipei');
        return date('Y-m-d H:i:s', $timestamp);
    }

    static function getLeftTimeFromInt($timestamp){
        date_default_timezone_set('Asia/Taipei');
        $now = new \DateTime('now');
        $left = $timestamp - $now->getTimestamp();
        $totalMin = floor($left / 60);
        $totalHour = floor($totalMin / 60);
        $totalDay = floor($totalHour / 24);
        return '剩'.$totalDay.'天'.($totalHour % 24).'小時'.($totalMin % 60).'分鐘';
    }

    static function getPassedTimeFromInt($timestamp){
        date_default_timezone_set('Asia/Taipei');
        $now = new \DateTime('now');
        $passed = $now->getTimestamp() - $timestamp;

        if($passed < 60) $passedFormat = $passed.'秒';
        else if($passed < 60 * 60) $passedFormat = floor($passed / 60).'分';
        else if($passed < (60 * 60 * 24)) $passedFormat = floor($passed / (60 * 60)).'時';
        else if($passed < (60 * 60 * 24 * 30)) $passedFormat = floor($passed / (60 * 60 * 24)).'日';
        else if($passed < (60 * 60 * 24 * 30 * 12)) $passedFormat = floor($passed / (60 * 60 * 24 * 30)).'月';
        else $passedFormat = floor($passed / (60 * 60 * 24 * 30 * 12)).'年';

        return $passedFormat.'前';
    }
}