@extends('layouts.without_header')

@section('title')
	{{ config('app.name') }}
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ URL::to('src/signinup/css/form-elements.css') }}">
    <link rel="stylesheet" href="{{ URL::to('src/signinup/css/style.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,100,300,500">
@endsection

@section('content')
    <div id="fb-root"></div>
    <script>
        window.fbAsyncInit = function() {
            FB.init({
            appId      : '217578548801139',
            cookie     : true,
            xfbml      : true,
            version    : 'v2.11'
            });
            FB.AppEvents.logPageView();
        };
        (function(d, s, id){
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) {return;}
        js = d.createElement(s); js.id = id;
        js.src = "https://connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>
    <div class="top-content">
        <div class="inner-bg">
            <div class="container">
                <div class="row">
                    <div class="col-sm-8 offset-sm-2 text">
                        <h1>Jugze</h1>
                        <h3>公共議題辯論平台</h3>
                        <div class="description">
                            <p>
                                <strong>發表議題、參與討論，暢所欲言</strong>
                            </p>
                            @if(count($errors) > 0)
                                <div class="alert alert-danger">
                                    @foreach($errors->all() as $error)
                                        <p>{{ $error }}</p>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-5">
                        <div class="form-box">
                            <div class="form-top">
                                <div class="form-top-left">
                                    <h3>登入</h3>
                                    <p>請輸入您的電子信箱、密碼</p>
                                </div>
                                <div class="form-top-right">
                                    <i class="fa fa-lock"></i>
                                </div>
                            </div>
                            <div class="form-bottom">
                                <form role="form" action="{{ route('user.signin') }}" method="post" class="login-form">
                                    <div class="form-group">
                                        <label class="sr-only" for="signin_account">電子信箱</label>
                                        <input type="text" name="signin_account" placeholder="jugze@your.email" class="signin_account form-control" id="signin_account">
                                    </div>
                                    <div class="form-group">
                                        <label class="sr-only" for="signin_password">密碼</label>
                                        <input type="password" name="signin_password" placeholder="至少四碼" class="signin_password form-control" id="signin_password">
                                    </div>
                                    <button type="submit" class="btn">登入！</button>
                                    {{ csrf_field() }}
                                </form>
                            </div>
                        </div>
                        @if (config('app.env') == 'live')
                            <div class="social-login">
                                <h3>或使用：</h3>
                                <div id="fbLogin" class="fb-login-button"
                                    data-max-rows="1" data-size="large"
                                    data-button-type="login_with" data-show-faces="false"
                                    data-auto-logout-link="false" data-use-continue-as="false"
                                    scope="public_profile,email"
                                    onlogin="checkLoginState();"
                                    >
                                    登入
                                </div>
                            </div>
                            <form role="form" action="{{ route('user.signinFB') }}" method="post" class="login-form">
                                <input type="text" id="fbId" name="fbId" value="" style="display:none;">
                                <input type="text" id="fbName" name="fbName" value="" style="display:none;">
                                <input type="text" id="fbEmail" name="fbEmail" value="" style="display:none;">
                                <button type="submit" id="fbSubmit" style="display:none;"></button>
                                {{ csrf_field() }}
                            </form>
                        @endif
                    </div>
                    <div class="col-sm-2"></div>
                    <div class="col-sm-5">

                        <div class="form-box">
                            <div class="form-top">
                                <div class="form-top-left">
                                    <h3>註冊</h3>
                                    <p>請填入以下資訊</p>
                                </div>
                                <div class="form-top-right">
                                    <i class="fa fa-pencil"></i>
                                </div>
                            </div>
                            <div class="form-bottom">
                                <form role="form" action="{{ route('user.signup') }}" method="post" class="registration-form">
                                    <div class="form-group">
                                        <label class="sr-only" for="signup_name">暱稱</label>
                                        <input type="text" name="signup_name" placeholder="如何稱呼您呢" class="signup_name form-control" id="signup_name">
                                    </div>
                                    <div class="form-group">
                                        <label class="sr-only" for="signup_account">電子信箱</label>
                                        <input type="text" name="signup_account" placeholder="jugze@your.email" class="signup_account form-control" id="signup_account">
                                    </div>
                                    <div class="form-group">
                                        <label class="sr-only" for="signup_password">密碼</label>
                                        <input type="password" name="signup_password" placeholder="至少四碼" class="signup_password form-control" id="signup_password">
                                    </div>
                                    <div class="form-group">
                                        <label class="sr-only" for="signup_again">再次輸入</label>
                                        <input type="password" name="signup_again" placeholder="請再次輸入密碼" class="signup_again form-control" id="signup_again">
                                    </div>
                                    <button type="submit" class="btn">立即註冊！</button>
                                    {{ csrf_field() }}
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ URL::to('src/signinup/js/scripts.js') }}"></script>
    <script src="{{ URL::to('src/signinup/js/fb.js') }}"></script>
    <script>
        function checkLoginState() {
            $(function () {
                FB.getLoginStatus(function(response) {
                    console.log(response);
                    if (response.status === 'connected'){
                        FB.api('/me?fields=id,name,email', function(response) {
                            console.log(JSON.stringify(response));
                            $('#fbId').val(response.id)
                            $('#fbName').val(response.name)
                            $('#fbEmail').val(response.email)
                            checkFBExist(response.id, response.name, response.email)
                        });
                    }
                });
            });
        }
    </script>
@endsection