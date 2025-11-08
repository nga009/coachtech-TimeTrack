@extends('layouts.default')

@section('title','出勤登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/create.css')}}">
@endsection

@section('content')
<div class="card">
    <!-- 状態 -->
    @if($status === 'not_working')
        <div class="status-badge">勤務外</div>
    @elseif($status === 'working')
        <div class="status-badge">出勤中</div>
    @elseif($status === 'on_break')
        <div class="status-badge">休憩中</div>
    @else
        <div class="status-badge">退勤済</div>
    @endif

    <!-- 表示用 -->
    <div class="date" id="dateDisplay">{{ now()->isoFormat('YYYY年M月D日(ddd)') }}</div>
    <div class="time" id="timeDisplay">{{ now()->format('H:i') }}</div>

    <!-- フォーム開始 -->
    @if($status === 'not_working')
        <form method="POST" action="{{ route('attendance.clock-in') }}">
            @csrf
            <button type="submit" class="attend-btn">出勤</button>
        </form>
    @elseif($status === 'working')
        <div class="working__btn">
            <form method="POST" action="{{ route('attendance.clock-out') }}">
                @csrf
                <button type="submit" class="attend-btn">退勤</button>
            </form>
            <form method="POST" action="{{ route('attendance.break-start') }}">
                @csrf
                <button type="submit" class="break-btn">休憩入</button>
            </form>
        </div>
    @elseif($status === 'on_break')
        <form method="POST" action="{{ route('attendance.break-end') }}">
            @csrf
            <button type="submit" class="break-btn">休憩戻</button>
        </form>
    @else
        <div class="workend_message">お疲れ様でした。</div>
    @endif
</div>
@endsection