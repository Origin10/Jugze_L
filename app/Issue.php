<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    public function subIssues(){
        return $this->hasMany('App\SubIssue');
    }
}
