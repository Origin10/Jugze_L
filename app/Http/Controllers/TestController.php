<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Constant;
use \Firebase\FirebaseLib;
use \App\Mail\CommentTag;
use Auth;
use Mail;

class TestController extends Controller
{
    public function testMail(){
        Mail::to("demo.or.test.0823@gmail.com")->send(new CommentTag('發信測試', 5, 'jugze.com/public'));
    }
}