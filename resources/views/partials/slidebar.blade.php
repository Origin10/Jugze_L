{{--  這邊是主要版型的左方導覽列  --}}
<div class="column col-sm-2 col-1 sidebar-offcanvas text-center" id="sidebar">
    <div class="nav flex-column">
        {{--  設定 logo 點擊後會回到首頁 issue.index  --}}
        <a href="{{ route('issue.index') }}">
            <img class="rounded mx-auto d-sm-block d-none" src="{{ URL::to('src/master/img/logo.png')}}" alt="logo" style="width: 80%;"/>
        </a>
    </div>
    <br>
    <div class="nav flex-column" id="lg-menu" aria-orientation="vertical">
        {{--  手機版型時 logo 會消失所以要多出一個 home 的圖示給使用者按  --}}
        <a href="{{ route('issue.index') }}" class="nav-link active d-inline d-sm-none">
            <i class="fa fa-home"></i>
        </a>
        {{--  單純顯示使用者身分  --}}
        <a href="#" class="nav-link d-sm-inline d-none">
            <i class="fa fa-user-circle" aria-hidden="true"></i>
            <strong>
                Hello,
                @if(Auth::check())
                    {{ Auth::user()->nickname }}
                @else
                    訪客
                @endif
            </strong>
        </a>
        {{--  使用者登入時可以用的功能  --}}
        @if(Auth::check())
            <form id="profile" role="form" action="{{ route('user.profile') }}" method="post" class="login-form">
                <input type="text" name="targetId" value="{{Auth::user()->id}}" style="display:none;">
                <a onclick="profile.submit();" href="javascript:;" class="nav-link active">
                    <i class="fa fa-user"></i>
                    <strong class="d-sm-inline d-none">我的檔案</strong>
                </a>
                {{ csrf_field() }}
            </form>
            <a href="{{ route('issue.createIssue') }}" class="nav-link active">
                <i class="fa fa-plus"></i>
                <strong class="d-sm-inline d-none">發佈議題</strong>
            </a>
            <a href="{{ route('message.getMessageIndexClear') }}" target="_blank" class="nav-link active">
                <i class="fa fa-comment"></i>
                <strong class="d-sm-inline d-none">開始聊天</strong>
            </a>
            <br>
            <a href="{{ route('user.logout') }}" class="nav-link active">
                <i class="fa fa-user-times"></i>
                <text class="d-sm-inline d-none">登出</text>
            </a>
        @else
        {{--  訪客只能登入或註冊  --}}
            <br>
            <a href="{{ route('user.signinup') }}" class="nav-link active">
                <i class="fa fa-user-plus"></i>
                <text class="d-sm-inline d-none">登入 | 註冊</text>
            </a>
        @endif
    </div>
</div>