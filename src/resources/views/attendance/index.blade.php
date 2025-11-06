@extends('layouts.default')

@section('title','勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/common.css')}}">
<link rel="stylesheet" href="{{ asset('css/attendance/index.css')}}">
@endsection

@section('content')
<div class="container">
    <div class="header">
        {{-- スタッフ --}}
        @if(auth()->user()->role === 'user')
            <h1>勤怠一覧</h1>
        @else
            <h1>{{ $user->name }}さんの勤怠</h1>
        @endif
    </div>

    <div class="date-navigation">
        <a href="?year={{ $month == 1 ? $year - 1 : $year }}&month={{ $month == 1 ? 12 : $month - 1 }}">← 前月</a>
        <div class="current-date">
            {{ $year }}/{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}
        </div>
        <a href="?year={{ $month == 12 ? $year + 1 : $year }}&month={{ $month == 12 ? 1 : $month + 1 }}">翌月 →</a>
    </div>

    <div class="attendance-table">
        <table>
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dates as $item)
                    <tr>
                        <td>{{ $item['date']->isoFormat('MM/DD(ddd)') }}</td>
                        <td>{{ $item['attendance'] && $item['attendance']->clock_in ? \Carbon\Carbon::parse($item['attendance']->clock_in)->format('H:i') : '' }}</td>
                        <td>{{ $item['attendance'] && $item['attendance']->clock_out ? \Carbon\Carbon::parse($item['attendance']->clock_out)->format('H:i') : '' }}</td>
                        <td>{{ $item['attendance'] ? floor($item['attendance']->getTotalBreakMinutes() / 60) . ':' . str_pad($item['attendance']->getTotalBreakMinutes() % 60, 2, '0', STR_PAD_LEFT) : '' }}</td>
                        <td>{{ $item['attendance'] ? $item['attendance']->getWorkHours() : '' }}</td>
                        <td class="detail-cell">
                            @if($item['attendance'])
                                @if(auth()->user()->role === 'user')
                                    <a href="{{ route('attendance.detail', $item['attendance']->id) }}">詳細</a>
                                @else
                                    <a href="{{ route('admin.attendance.show', $item['attendance']->id) }}">詳細</a>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(auth()->user()->role === 'admin')
        <form class="form-export" method="POST" action="{{ route('admin.staff.monthly.export', $user->id) }}">
            @csrf
            <div class="button-container">
                <button class="submit-btn csv-btn">CSV出力</button>
            </div>
        </form>
    @endif
</div>
@endsection