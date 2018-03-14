<?php

namespace App\Http\Controllers;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use \App\Constant;
use \App\User;
use \App\UserIssue;
use \App\Issue;
use \App\SubIssue;
use \Firebase\FirebaseLib;

class WebSocketController extends Controller implements MessageComponentInterface{
    private $connections = [];

    /**
     * When a new connection is opened it will be passed to this method
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     * @throws \Exception
     */
    function onOpen(ConnectionInterface $conn){
        echo $conn->resourceId.": onOpen\n";
        $this->connections[$conn->resourceId] = compact('conn') +
            ['user_id' => null, 'issue_id' => null, 'sub_issue_id' => null, 'mode' => null];
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Exception
     */
    function onClose(ConnectionInterface $conn){
        $disconnectedId = $conn->resourceId;
        unset($this->connections[$disconnectedId]);
        // foreach($this->connections as &$connection)
        //     $connection['conn']->send(json_encode([
        //         'offline_user' => $disconnectedId,
        //         'from_user_id' => 'server control',
        //         'from_resource_id' => null
        //     ]));
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param  ConnectionInterface $conn
     * @param  \Exception $e
     * @throws \Exception
     */
    function onError(ConnectionInterface $conn, \Exception $e){
        $userId = $this->connections[$conn->resourceId]['user_id'];
        echo "An error has occurred with user $userId: {$e->getMessage()}\n";
        unset($this->connections[$conn->resourceId]);
        $conn->close();
    }

    /**
     * Triggered when a client sends data through the socket
     * @param  \Ratchet\ConnectionInterface $conn The socket/connection that sent the message to your application
     * @param  string $msg The message received
     * @throws \Exception
     */
    function onMessage(ConnectionInterface $conn, $msg){
        if(is_null($this->connections[$conn->resourceId]['user_id']) ||
            is_null($this->connections[$conn->resourceId]['mode'])
        ){
            $firstMeet = \json_decode($msg);
            if($firstMeet->type == Constant::WEBSOCKET_TYPE_FIRST_MEET){
                $this->connections[$conn->resourceId]['user_id'] = \intval(\base64_decode($firstMeet->user_id));
                $this->connections[$conn->resourceId]['issue_id'] = (isset($firstMeet->issue_id) ? $firstMeet->issue_id : null);
                $this->connections[$conn->resourceId]['sub_issue_id'] = (isset($firstMeet->sub_issue_id) ? $firstMeet->sub_issue_id : null);
                $this->connections[$conn->resourceId]['mode'] = $firstMeet->mode;
                echo 'user:'.\base64_decode($firstMeet->user_id).
                    ' in issue:'.(isset($firstMeet->issue_id) ? $firstMeet->issue_id : null).
                    ' in sub issue:'.(isset($firstMeet->sub_issue_id) ? $firstMeet->sub_issue_id : null).
                    ' mode:'.$firstMeet->mode."\n";
            }
            // $onlineUsers = [];
            // foreach($this->connections as $resourceId => &$connection){
            //     $connection['conn']->send(json_encode([$conn->resourceId => $msg]));
            //     if($conn->resourceId != $resourceId)
            //         $onlineUsers[$resourceId] = $connection['user_id'];
            // }
            // $conn->send(json_encode(['online_users' => $onlineUsers]));
        } else if($this->connections[$conn->resourceId]['mode'] == Constant::WEBSOCKET_TYPE_COMMENT_ISSUE){
            $fromUserId = $this->connections[$conn->resourceId]['user_id'];
            $fromIssueId = $this->connections[$conn->resourceId]['issue_id'];
            $mode = $this->connections[$conn->resourceId]['mode'];

            echo 'user:'.$fromUserId.' in issue:'.$fromIssueId.' msg:'.$msg."\n";

            $userInIssue = User::find($fromUserId)->userIssues->where('issue_id', $fromIssueId)->first();

            echo $fromUserId;

            $now = new \DateTime('now');
            $now = $now->getTimestamp();

            $firebase = new FirebaseLib(Constant::getFirebaseDefaultURL(), Constant::FIREBASE_DEFAULT_TOKEN);
            $storedMsg = array(
                'user_id' => $fromUserId,
                'status' => $userInIssue->status,
                'comment' => $msg
            );
            $firebase->set(Constant::getFirebaseDefaultPath($mode).
                    $fromIssueId.'/'.
                    $now.'_'.$fromUserId
                , $storedMsg);

            foreach($this->connections as $resourceId => &$connection){
                if($connection['mode'] == $mode && $connection['issue_id'] == $fromIssueId){
                    echo $connection['user_id'];
                    $connection['conn']->send(
                        \json_encode([
                            'myself' => $connection['user_id'],
                            'mode' => $mode,
                            'userInfo' => [
                                'id' => $userInIssue->user_id,
                                'issueId' => $userInIssue->issue_id,
                                'name' => $userInIssue->nickname->name,
                                'img' => $userInIssue->nickname->img,
                                'color' => $userInIssue->color,
                                'seq' => $userInIssue->seq,
                                'status' => $userInIssue->status
                            ],
                            'id' => $now.'_'.$fromUserId,
                            'comment' => $msg,
                            'created_at' => Constant::getPassedTimeFromInt($now)
                        ])
                    );
                }

            }
        } else if($this->connections[$conn->resourceId]['mode'] == Constant::WEBSOCKET_TYPE_COMMENT_SUB_ISSUE){
            $fromUserId = $this->connections[$conn->resourceId]['user_id'];
            $fromSubIssueId = $this->connections[$conn->resourceId]['sub_issue_id'];
            $mode = $this->connections[$conn->resourceId]['mode'];

            echo 'user:'.$fromUserId.' in subIssue:'.$fromSubIssueId.' msg:'.$msg."\n";

            $subIssue = SubIssue::find($fromSubIssueId);

            $userInIssue = User::find($fromUserId)->userIssues->where('issue_id', $subIssue->issue_id)->first();
            $userInSubIssue = User::find($fromUserId)->userSubIssues->where('sub_issue_id', $fromSubIssueId)->first();

            $now = new \DateTime('now');
            $now = $now->getTimestamp();

            $firebase = new FirebaseLib(Constant::getFirebaseDefaultURL(), Constant::FIREBASE_DEFAULT_TOKEN);
            $storedMsg = array(
                'user_id' => $fromUserId,
                'status' => $userInSubIssue->status,
                'comment' => $msg
            );
            $firebase->set(Constant::getFirebaseDefaultPath($mode).
                    $fromSubIssueId.'/'.
                    $now.'_'.$fromUserId
                , $storedMsg);

            foreach($this->connections as $resourceId => &$connection){
                if($connection['mode'] == $mode && $connection['sub_issue_id'] == $fromSubIssueId){
                    $connection['conn']->send(
                        \json_encode([
                            'myself' => $connection['user_id'],
                            'mode' => $mode,
                            'userInfo' => [
                                'id' => $userInIssue->user_id,
                                'issueId' => $userInIssue->issue_id,
                                'subIssueId' => $subIssue->id,
                                'name' => $userInIssue->nickname->name,
                                'img' => $userInIssue->nickname->img,
                                'color' => $userInIssue->color,
                                'seq' => $userInIssue->seq,
                                'status' => $userInSubIssue->status
                            ],
                            'id' => $now.'_'.$fromUserId,
                            'comment' => $msg,
                            'created_at' => Constant::getPassedTimeFromInt($now)
                        ])
                    );
                }

            }
        }
        else if($this->connections[$conn->resourceId]['mode'] == Constant::WEBSOCKET_TYPE_MESSAGE){
            $fromUserId = $this->connections[$conn->resourceId]['user_id'];
            $mode = $this->connections[$conn->resourceId]['mode'];

            echo 'user:'.$fromUserId.' in message msg:'.$msg."\n";

            $data = \json_decode($msg);
            $targetUserIssue = UserIssue::where('issue_id', $data->targetIssueId)
                ->where('user_id', $data->targetUserId)->first();
            $targetUserIssue->nickname = $targetUserIssue->nickname()->first();
            $myselfUserIssue = UserIssue::where('issue_id', $data->targetIssueId)
                ->where('user_id', $fromUserId)->first();
            $myselfUserIssue->nickname = $myselfUserIssue->nickname()->first();

            $issue = Issue::find($targetUserIssue->issue_id);

            $now = new \DateTime('now');
            $now = $now->getTimestamp();

            $firebase = new FirebaseLib(Constant::getFirebaseDefaultURL(), Constant::FIREBASE_DEFAULT_TOKEN);
            $storedMsg = array(
                'content' => $data->content,
                'status' => $targetUserIssue->status,
                'read' => true
            );
            //先存發送者方
            $firebase->set(Constant::getFirebaseDefaultPath($mode).
                    'user_'.$fromUserId.'/'.
                    'issue_'.$targetUserIssue->issue_id.'/'.
                    'user_'.$targetUserIssue->user_id.'/'.
                    $now.'_send'
                , $storedMsg);

            //再存收件方
            $storedMsg['read'] = false;
            $firebase->set(Constant::getFirebaseDefaultPath($mode).
                'user_'.$targetUserIssue->user_id.'/'.
                'issue_'.$targetUserIssue->issue_id.'/'.
                'user_'.$fromUserId.'/'.
                $now.'_get'
                , $storedMsg);
            $firebase->set(Constant::getFirebaseDefaultPath($mode).
                'user_'.$targetUserIssue->user_id.'/'.
                'issue_'.$targetUserIssue->issue_id.'/'.
                'user_'.$fromUserId.'/'.
                'has_new_message'
                , true);

            $firebase->set(Constant::getFirebaseDefaultPath($mode).
                'user_'.$targetUserIssue->user_id.'/'.
                'has_new_message'
                , true);

            foreach($this->connections as $resourceId => &$connection){
                if($connection['mode'] == $mode){
                    if($connection['user_id'] == $fromUserId){
                        $connection['conn']->send(
                            \json_encode([
                                'mode' => $mode,
                                'targetUserId' => $targetUserIssue->user_id,
                                'targetIssueId' => $targetUserIssue->issue_id,
                                'issueTitle' => $issue->title,
                                'type' => 'send',
                                'content' => $data->content,
                                'targetUserInfo' => [
                                    'name' => $targetUserIssue->nickname->name,
                                    'img' => $targetUserIssue->nickname->img,
                                    'color' => $targetUserIssue->color,
                                    'seq' => $targetUserIssue->seq,
                                    'status' => $targetUserIssue->status
                                ],
                                'created_at' => Constant::getPassedTimeFromInt($now)
                            ])
                        );
                    }
                    else if($connection['user_id'] == $targetUserIssue->user_id){
                        $connection['conn']->send(
                            \json_encode([
                                'mode' => $mode,
                                'targetUserId' => $myselfUserIssue->user_id,
                                'targetIssueId' => $myselfUserIssue->issue_id,
                                'issueTitle' => $issue->title,
                                'type' => 'get',
                                'content' => $data->content,
                                'targetUserInfo' => [
                                    'name' => $myselfUserIssue->nickname->name,
                                    'img' => $myselfUserIssue->nickname->img,
                                    'color' => $myselfUserIssue->color,
                                    'seq' => $myselfUserIssue->seq,
                                    'status' => $myselfUserIssue->status
                                ],
                                'created_at' => Constant::getPassedTimeFromInt($now)
                            ])
                        );
                    }
                }
            }
        }
    }
}
