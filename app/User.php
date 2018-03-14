<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'pwd', 'fb_id', 'email', 'nickname', 'is_comment_public', 'groups'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'pwd', 'remember_token',
    ];

    public function getAuthPassword(){
        return $this->pwd;
    }

    public function issues(){
        return $this->hasMany('App\Issue');
    }

    public function subIssues(){
        return $this->hasMany('App\SubIssue');
    }

    public function userIssues(){
        return $this->hasMany('App\UserIssue');
    }

    public function userSubIssues(){
        return $this->hasMany('App\UserSubIssue');
    }
}
