<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [
	'uses' => 'IssueController@getIndex',
	'as' => 'issue.index'
]);

Route::group(['prefix' => 'issue'], function(){

	Route::get('/detail/{id}', [
		'uses' => 'IssueController@getIssueDetail',
		'as' => 'issue.detail'
	]);

	Route::get('/extend/{id}', [
		'uses' => 'SubIssueController@getSubIssueDetail',
		'as' => 'issue.extend'
	]);

	Route::group(['middleware' => 'auth'], function(){
		Route::get('/createIssue', [
			'uses' => 'IssueController@getCreateIssue',
			'as' => 'issue.createIssue'
		]);

		Route::post('/createIssue', [
			'uses' => 'IssueController@postCreateIssue',
			'as' => 'issue.createIssue'
		]);

		Route::post('/editIssue', [
			'uses' => 'IssueController@postEditIssue',
			'as' => 'issue.editIssue'
		]);

		Route::get('/join', [
			'uses' => 'IssueController@getJoinIssue',
			'as' => 'issue.join'
		]);

		Route::get('/becomeJudge', [
			'uses' => 'IssueController@getBecomeJudge',
			'as' => 'issue.becomeJudge'
		]);

		Route::get('/vote/{id}', [
			'uses' => 'SubIssueVoteController@getSubIssueVote',
			'as' => 'issue.subIssueVote'
		]);

		Route::post('/createSubIssueVote', [
			'uses' => 'SubIssueVoteController@postCreateSubIssueVote',
			'as' => 'issue.createSubIssueVote'
		]);

		Route::post('/voteSubIssue', [
			'uses' => 'SubIssueVoteController@postVoteSubIssue',
			'as' => 'issue.voteSubIssue'
		]);

		Route::get('/joinExtend', [
			'uses' => 'SubIssueController@getJoinSubIssue',
			'as' => 'issue.joinExtend'
		]);

		Route::post('/judgeComment', [
			'uses' => 'IssueJSPostController@postJudge',
			'as' => 'issue.judgeComment'
		]);

		Route::post('/judgeCommentReply', [
			'uses' => 'IssueJSPostController@postJudgeReply',
			'as' => 'issue.judgeCommentReply'
		]);

		Route::post('/editComment', [
			'uses' => 'IssueJSPostController@postEditComment',
			'as' => 'issue.editComment'
		]);

		Route::post('/deleteComment', [
			'uses' => 'IssueJSPostController@postDeleteComment',
			'as' => 'issue.deleteComment'
		]);

		Route::post('/thumbChange', [
			'uses' => 'IssueJSPostController@postThumbChange',
			'as' => 'issue.thumbChange'
		]);

		Route::post('/tagFloor', [
			'uses' => 'IssueJSPostController@postTagFloor',
			'as' => 'issue.tagFloor'
		]);

		Route::post('/extendJudgeComment', [
			'uses' => 'SubIssueJSPostController@postJudge',
			'as' => 'extend.judgeComment'
		]);

		Route::post('/extendJudgeCommentReply', [
			'uses' => 'SubIssueJSPostController@postJudgeReply',
			'as' => 'extend.judgeCommentReply'
		]);

		Route::post('/extendEditComment', [
			'uses' => 'SubIssueJSPostController@postEditComment',
			'as' => 'extend.editComment'
		]);

		Route::post('/extendDeleteComment', [
			'uses' => 'SubIssueJSPostController@postDeleteComment',
			'as' => 'extend.deleteComment'
		]);

		Route::post('/extendThumbChange', [
			'uses' => 'SubIssueJSPostController@postThumbChange',
			'as' => 'extend.thumbChange'
		]);

		Route::post('/extendTagFloor', [
			'uses' => 'SubIssueJSPostController@postTagFloor',
			'as' => 'extend.tagFloor'
		]);
	});
});

Route::group(['prefix' => 'user'], function(){
	Route::group(['middleware' => 'guest'], function(){
		Route::get('/signinup', [
			'uses' => 'UserController@getSignInUp',
			'as' => 'user.signinup'
		]);

		Route::post('/checkFB', [
			'uses' => 'UserController@postCheckFB',
			'as' => 'user.checkFB'
		]);

		Route::post('/signup', [
			'uses' => 'UserController@postSignUp',
			'as' => 'user.signup'
		]);

		Route::post('/signupFB', [
			'uses' => 'UserController@postSignUpFB',
			'as' => 'user.signupFB'
		]);

		Route::post('/signin', [
			'uses' => 'UserController@postSignIn',
			'as' => 'user.signin'
		]);

		Route::post('/signinFB', [
			'uses' => 'UserController@postSignInFB',
			'as' => 'user.signinFB'
		]);
	});

	Route::group(['middleware' => 'auth'], function(){
		Route::post('/profile', [
			'uses' => 'UserController@postProfile',
			'as' => 'user.profile'
		]);

		Route::get('/logout', [
			'uses' => 'UserController@getLogout',
			'as' => 'user.logout'
		]);

		Route::post('/profileChangeStatus', [
			'uses' => 'UserController@postChangeStatus',
			'as' => 'user.profileChangeStatus'
		]);

		Route::post('/profileChangePublic', [
			'uses' => 'UserController@postChangePublic',
			'as' => 'user.postChangePublic'
		]);
	});
});

Route::group(['prefix' => 'message'], function(){

	Route::group(['middleware' => 'auth'], function(){
		Route::get('index', [
			'uses' => 'MessageController@getMessageIndex',
			'as' => 'message.getMessageIndexClear'
		]);

		Route::get('index/{targetId}', [
			'uses' => 'MessageController@getMessageIndex',
			'as' => 'message.getMessageIndex'
		]);

		Route::post('messages', [
			'uses' => 'MessageController@postMessageContent',
			'as' => 'message.postMessageContent'
		]);
	});
});

Route::group(['middleware' => 'auth'], function(){
	Route::post('myself', [
		'uses' => 'UserController@postUserId',
		'as' => 'message.postUserId'
	]);
});

Route::get('test/mail', [
	'uses' => 'TestController@testMail',
	'as' => 'test.testMail'
]);