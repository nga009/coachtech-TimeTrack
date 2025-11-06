<!DOCTYPE html>
<html lang="jp">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css')}}">
    <link rel="stylesheet" href="{{ asset('css/common.css')}}">
    @yield('css')
</head>

<body>
    <header>
        <div class="header__logo">
            <img src="{{ asset('images/logo.svg')}}">
        </div>
        <nav class="header__nav">
            <ul>
                @if( !in_array(Route::currentRouteName(), ['register', 'login', 'verification.notice', 'admin.login']) )
                    {{-- スタッフ --}}
                    @if(auth()->user()->role === 'user')
                        @if( auth()->user()->getAttendanceStatus() !== 'finished' )
                            <li>
                                <a href="{{ route('attendance.create') }}" class="button-link" >勤怠</a>
                            </li>
                            <li>
                                <a href="{{ route('attendance.index') }}" class="button-link" >勤怠一覧</a>
                            </li>
                            <li>
                                <a href="{{ route('request.index') }}" class="button-link" >申請</a>
                            </li>
                        @else
                            {{-- スタッフ 退勤画面のみ表示 --}}
                            <li>
                                <a href="{{ route('attendance.index') }}" class="button-link" >今月の出勤一覧</a>
                            </li>
                            <li>
                                <a href="{{ route('request.index') }}" class="button-link" >申請一覧</a>
                            </li>
                        @endif
                        <li>
                            <form action="/logout" method="post">
                                @csrf
                                <input type="hidden" name="context" value="user">
                                <button class="header__logout">ログアウト</button>
                            </form>
                        </li>
                    @else
                        {{-- 管理者 --}}
                        <li>
                            <a href="{{ route('admin.attendance.daily') }}" class="button-link" >勤怠一覧</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.staff.index') }}" class="button-link" >スタッフ一覧</a>
                        </li>
                        <li>
                            <a href="{{ route('request.index') }}" class="button-link" >申請一覧</a>
                        </li>
                        <li>
                            <form action="/logout" method="post">
                                @csrf
                                <input type="hidden" name="context" value="admin">
                                <button class="header__logout">ログアウト</button>
                            </form>
                        </li>
                    @endif
                @endif
            </ul>
        </nav>
    </header>
    <main class="content">
        @yield('content')
    </main>
    @yield('scripts')
</body>
</html>