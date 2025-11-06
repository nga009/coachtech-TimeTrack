@extends('layouts.default')

@section('title','勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/common.css')}}">
<link rel="stylesheet" href="{{ asset('css/attendance/index.css')}}">
@endsection

@section('content')
<div class="container">
    <div class="header">
        <h1>{{ $date->format('Y年m月d日') }}の勤怠</h1>
    </div>

    <div class="date-navigation">
        <a href="?date={{ $date->copy()->subDay()->format('Y-m-d') }}">← 前日</a>
        <div class="current-date">
            {{ $date->format('Y/m/d') }}
        </div>
        <a href="?date={{ $date->copy()->addDay()->format('Y-m-d') }}">翌日 →</a>
    </div>

    <div class="attendance-table">
        <table>
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $attendance)
                    <tr>
                        <td>
                            {{ $attendance->user->name }}
                        </td>
                        <td>
                            {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
                        </td>
                        <td>
                            {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
                        </td>
                        <td>
                            {{ floor($attendance->getTotalBreakMinutes() / 60) }}:{{ str_pad($attendance->getTotalBreakMinutes() % 60, 2, '0', STR_PAD_LEFT) }}
                        </td>
                        <td>
                            {{ $attendance->getWorkHours() }}
                        </td>
                        <td class="detail-cell">
                            <a href="{{ route('admin.attendance.show', $attendance->id) }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection