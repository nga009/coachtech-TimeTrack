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

    <form method="POST" action="{{ auth()->user()->role === 'admin' && !$attendance->request ? route('admin.attendance.update', $attendance->id) : route('attendance.request', $attendance->id) }}">
        @csrf
        <div class="detail-card">
            <div class="detail-row">
                <div class="label">名前</div>
                <div class="value">{{ $attendance->user->name }}</div>
            </div>

            <div class="detail-row">
                <div class="label">日付</div>
                <div class="value">
                    <div class="date-group">
                        <span>{{ $attendance->date->format('Y年') }}</span>
                        <span>{{ $attendance->date->format('m月d日') }}</span>
                    </div>
                </div>
            </div>

            <div class="detail-row">
                <div class="label">出勤・退勤</div>
                <div class="value">
                    <div class="time-range">
                        @if($attendance->request)
                            <span>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</span>
                            <span class="separator">〜</span>
                            <span class="time-change">{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</span>
                        @else
                            <input type="text" name="clock_in" value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
                            <span class="separator">〜</span>
                            <input type="text" name="clock_out" value="{{ old('clock_out',$attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
                        @endif
                    </div>
                    @if($errors->has('clock_in') || $errors->has('clock_out'))
                        <div class="form__error">
                            {{ $errors->first('clock_in') ?: $errors->first('clock_out') }}
                        </div>
                    @endif
                </div>
            </div>

            @foreach($attendance->breaks as $index => $break)
            <div class="detail-row">
                <div class="label">休憩{{ $index + 1 }}</div>
                <div class="value">
                    @if($attendance->request)
                        <div class="time-range">
                            <span class="time-change">{{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}</span>
                            <span class="separator">〜</span>
                            <span class="time-change">{{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}</span>
                        </div>
                    @else
                        <div class="time-range">
                            <input type="text" name="breaks[{{ $index }}][start]" value="{{ old('breaks.'.$index.'.start', $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}">
                            <span class="separator">〜</span>
                            <input type="text" name="breaks[{{ $index }}][end]" value="{{ old('breaks.'.$index.'.end', $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">
                        </div>
                        @if($errors->has('breaks.'.$index.'.start') || $errors->has('breaks.'.$index.'.end'))
                            <div class="form__error">
                                {{ $errors->first('breaks.'.$index.'.start') ?: $errors->first('breaks.'.$index.'.end') }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
            @endforeach

            <div class="detail-row">
                <div class="label">休憩{{ count($attendance->breaks) + 1 }}</div>
                <div class="value">
                    <div class="time-range">
                        @if(!$attendance->request)
                            <input type="text" name="breaks[{{ count($attendance->breaks) }}][start]" value="{{ old('breaks.'.count($attendance->breaks).'.start', '') }}">
                            <span class="separator">〜</span>
                            <input type="text" name="breaks[{{ count($attendance->breaks) }}][end]" value="{{ old('breaks.'.count($attendance->breaks).'.end', '') }}" >
                        @endif
                    </div>
                    @if($errors->has('breaks.'.count($attendance->breaks).'.start') || $errors->has('breaks.'.count($attendance->breaks).'.end'))
                        <div class="form__error">
                            {{ $errors->first('breaks.'.count($attendance->breaks).'.start') ?: $errors->first('breaks.'.count($attendance->breaks).'.end') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="detail-row">
                <div class="label">備考</div>
                <div class="value">
                    @if($attendance->request)
                        {{ $attendance->memo }}
                    @else
                        <textarea name="memo">{{ old('memo', $attendance->memo) }}</textarea>
                        @if($errors->has('memo'))
                            <div class="form__error">
                                {{ $errors->first('memo') }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="button-container">
            @if($attendance->request)
                <div class="pending-message">*承認待ちのため修正はできません。</div>
            @else
                <button class="submit-btn">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection