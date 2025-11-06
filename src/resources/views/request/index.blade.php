@extends('layouts.default')

@section('title','申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/common.css')}}">
<link rel="stylesheet" href="{{ asset('css/attendance/index.css')}}">
@endsection

@section('content')
<div class="container">
    <div class="header">
        <h1>申請一覧</h1>
    </div>

    <div class="border">
        <ul class="border__list">
            <li><a class="nav-link {{ $page === 'pending' ? 'active' : '' }}" href="{{ route('request.index') }}?page=pending">承認待ち</a></li>
            <li><a class="nav-link {{ $page === 'approved' ? 'active' : '' }}"" href="{{ route('request.index') }}?page=approved">承認済み</a></li>
        </ul>
    </div>

    <div class="attendance-table">
        <table>
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $request)
                <tr>
                    <td>
                        @if($request->status === 'pending')
                            承認待ち
                        @else
                            承認済み
                        @endif
                    </td>
                    <td>
                        {{ $request->user->name }}
                    </td>
                    <td class="date-cell">
                        {{ $request->attendance->date->format('Y/m/d') }}
                    </td>
                    <td>
                        {{ $request->memo }}
                    </td>
                    <td class="date-cell">
                        {{ $request->created_at->format('Y/m/d') }}
                    </td>
                    <td class="detail-cell">
                        <a href="{{ route('request.show', $request->id) }}">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection