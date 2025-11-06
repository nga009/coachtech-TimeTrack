@extends('layouts.default')

@section('title','ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/auth.css')}}">
@endsection

@section('content')
<form action="/login" method="post" class="authenticate center">
    @csrf
    <h1 class="page__title">{{ $title ?? 'ログイン' }}</h1>
    <input type="hidden" name="login_type" value="{{ $loginType ?? 'user' }}">
    <label for="mail" class="login__name">メールアドレス</label>
    <input name="email" id="mail" type="text" class="login__input" value="{{ old('email') }}">
    <div class="form__error">
        @error('email')
        {{ $message }}
        @enderror
    </div>
    <label for="password" class="login__name">パスワード</label>
    <input name="password" id="password" type="password" class="login__input">
    <div class="form__error">
        @error('password')
        {{ $message }}
        @enderror
    </div>
    <button class="btn btn--big">ログインする</button>
    @if( in_array(Route::currentRouteName(), ['login']) )
        <a href="/register" class="link">会員登録はこちら</a>
    @endif
</form>
@endsection