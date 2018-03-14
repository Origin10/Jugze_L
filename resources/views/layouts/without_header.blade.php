{{--  HTML上面的宣告，一定要使用  --}}
<!doctype html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html" charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link rel="shortcut icon" href="{{ URL::to('ico/favicon.ico') }}" />
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="{{ URL::to('ico/favicon-144.ico') }}">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{{ URL::to('ico/favicon-114.ico') }}">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{{ URL::to('ico/favicon-72.ico') }}">
    <link rel="apple-touch-icon-precomposed" href="{{ URL::to('ico/favicon-57.ico') }}">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://use.fontawesome.com/48187b95c0.js"></script>
    <script src="{{ URL::to('src/STATUS.js') }}"></script>
    <script src="{{ URL::to('src/Constant.js') }}"></script>
    <base href="{{ URL::to('./') }}">
    {{--  <link rel="stylesheet" href="{{ URL::to('src/css/app.css') }}">  --}}
    @yield('styles')
</head>
<body>
@yield('header')

@yield('content')

@yield('scripts')
</body>
</html>