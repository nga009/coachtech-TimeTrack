@extends('layouts.default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify.css')}}">
@endsection

@section('content')
<div class="container">
    
    <div class="content__heading">
        <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
        <p>メール認証を完了してください。</p>
    </div>

    <div class="content__emailverify">
        <div class="button-wrapper">
            <a href="http://localhost:8025" class="button-link">認証はこちらから</a>
        </div>    
    </div>

    <div class="verify-form__resend">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="">認証メールを再送する
            </button>
        </form>
    </div>
</div>
@endsection