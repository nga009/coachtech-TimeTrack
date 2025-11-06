@extends('layouts.default')

@section('title','勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/common.css')}}">
<link rel="stylesheet" href="{{ asset('css/attendance/form.css')}}">
@endsection

@section('content')
<div class="container">
    <div class="header">
        <h1>勤怠詳細</h1>
    </div>

    <form method="POST" action="{{ route('admin.request.approve', $request->id) }}">
        @csrf
        <div class="detail-card">
            <div class="detail-row">
                <div class="label">名前</div>
                <div class="value">{{ $request->user->name }}</div>
            </div>

            <div class="detail-row">
                <div class="label">日付</div>
                <div class="value">
                    <div class="date-group">
                        <span>{{ $request->attendance->date->format('Y年') }}</span>
                        <span>{{ $request->attendance->date->format('m月d日') }}</span>
                    </div>
                </div>
            </div>

            <div class="detail-row">
                <div class="label">出勤・退勤</div>
                <div class="value">
                    <div class="time-range">
                        <span>{{ $request->requested_clock_in ? \Carbon\Carbon::parse($request->requested_clock_in)->format('H:i') : '-' }}</span>
                        <span class="separator">〜</span>
                        <span class="time-change">{{ $request->requested_clock_out ? \Carbon\Carbon::parse($request->requested_clock_out)->format('H:i') : '-' }}</span>
                    </div>
                </div>
            </div>

            @foreach($request->requested_breaks ?? [] as $index => $break)
            <div class="detail-row">
                <div class="label">休憩{{ $index + 1 }}</div>
                <div class="value">
                    @if(!empty($break['start']) && !empty($break['end']))
                        <div class="time-range">
                            <span class="time-change">{{ $break['start'] }}</span>
                            <span class="separator">〜</span>
                            <span class="time-change">{{ $break['end'] }}</span>
                        </div>
                    @endif
                </div>
            </div>
            @endforeach

            <div class="detail-row">
                <div class="label">休憩{{ count($request->requested_breaks) + 1 }}</div>
                <div class="value">
                    <div class="time-range">
                    </div>
                </div>
            </div>

            <div class="detail-row">
                <div class="label">備考</div>
                <div class="value">
                    {{ $request->memo }}
                </div>
            </div>
        </div>

        <div class="button-container">
            @if($request->status == 'pending')
                @if(auth()->user()->role === 'admin')
                    <button class="submit-btn">承認</button>
                @else
                    <div class="pending-message">*承認待ちのため修正はできません。</div>
                @endif
            @else
                <button class="submit-btn" disabled>承認済み</button>
            @endif
        </div>
    </form>
</div>
@endsection