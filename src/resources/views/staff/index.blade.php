@extends('layouts.default')

@section('title','スタッフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/common.css')}}">
<link rel="stylesheet" href="{{ asset('css/attendance/index.css')}}">
@endsection

@section('content')
<div class="container">
    <div class="header">
        <h1>スタッフ一覧</h1>
    </div>

    <div class="attendance-table">
        <table>
            <thead>
                <tr>
                    <th class="name-header">名前</th>
                    <th class="email-header">メールアドレス</th>
                    <th class="detail-header">月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td class="detail-cell"><a href="{{ route('admin.staff.monthly', $user->id) }}">詳細</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection