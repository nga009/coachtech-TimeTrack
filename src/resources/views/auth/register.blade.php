@extends('layouts.default')

@section('title','会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/auth.css')}}">
@endsection

@section('content')
<form action="/register" method="post" class="authenticate center">
    @csrf
    <h1 class="page__title">会員登録</h1>
    <label for="name" class="entry__name">名前</label>
    <input name="name" id="name" type="text" class="entry__input" value="{{ old('name') }}">
    <div class="form__error">
        @error('name')
        {{ $message }}
        @enderror
    </div>
    <label for="mail" class="entry__email">メールアドレス</label>
    <input name="email" id="mail" type="text" class="entry__input" value="{{ old('email') }}">
    <div class="form__error">
        @error('email')
        {{ $message }}
        @enderror
    </div>
    <label for="password" class="entry__password">パスワード</label>
    <input name="password" id="password" type="password" class="entry__input">
    <div class="form__error">
        @error('password')
        {{ $message }}
        @enderror
    </div>
    <label for="password_confirm" class="entry__password">パスワード確認</label>
    <input name="password_confirmation" id="password_confirm" type="password" class="entry__input">
    <div class="form__error">
        @error('password_confirmation')
        {{ $message }}
        @enderror
    </div>
    <button class="btn btn--big">登録する</button>
    <a href="/login" class="link">ログインはこちら</a>
</form>
@endsection