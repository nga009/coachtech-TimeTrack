<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\WorkBreak;
use App\Http\Requests\AttendanceEditRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 勤怠管理画面
    public function create()
    {
        $user = auth()->user();
        $today = Carbon::today();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $status = 'not_working';
        $currentBreak = null;

        if ($attendance) {
            if (!$attendance->clock_out) {
                $currentBreak = $attendance->breaks()
                    ->whereNull('break_end')
                    ->first();
                if ($currentBreak) {
                    $status = 'on_break';
                } else {
                    $status = 'working';
                }
            } else {
                $status = 'finished';
            }
        }

        return view('attendance.create', compact('attendance', 'status', 'currentBreak'));
    }

    // 出勤
    public function clockIn()
    {
        $user = auth()->user();
        $today = Carbon::today();

        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $user->id,
                'date' => $today,
            ],
            [
                'clock_in' => Carbon::now()->format('H:i:s'),
            ]
        );

        return redirect()->route('attendance.create');
    }

    // 退勤
    public function clockOut()
    {
        $user = auth()->user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance && !$attendance->clock_out) {
            $attendance->update([
                'clock_out' => Carbon::now()->format('H:i:s'),
            ]);

            // 未完了の休憩を終了
            $attendance->breaks()
                ->whereNull('break_end')
                ->update(['break_end' => Carbon::now()->format('H:i:s')]);
        }

        return redirect()->route('attendance.create');
    }

    // 休憩開始
    public function breakStart()
    {
        $user = auth()->user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance) {
            WorkBreak::create([
                'attendance_id' => $attendance->id,
                'break_start' => Carbon::now()->format('H:i:s'),
            ]);
        }

        return redirect()->route('attendance.create');
    }

    // 休憩終了
    public function breakEnd()
    {
        $user = auth()->user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance) {
            $break = $attendance->breaks()
                ->whereNull('break_end')
                ->first();

            if ($break) {
                $break->update([
                    'break_end' => Carbon::now()->format('H:i:s'),
                ]);
            }
        }

        return redirect()->route('attendance.create');
    }

    // 月次一覧
    public function monthly(Request $request)
    {
        $user = auth()->user();
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get()
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        $dates = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            $dates[] = [
                'date' => $current->copy(),
                'attendance' => $attendances->get($dateStr),
            ];
            $current->addDay();
        }

        return view('attendance.index', compact('dates', 'year', 'month'));
    }

    // 詳細
    public function show($id)
    {
        $attendance = Attendance::with('breaks', 'request')->findOrFail($id);

        return view('attendance.form', compact('attendance'));
    }

    // 管理者：直接修正
    public function update(AttendanceEditRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $validated = $request->validated();

        $attendance->update([
            'clock_in' => $validated['clock_in'],
            'clock_out' => $validated['clock_out'],
            'memo' => $validated['memo'],
        ]);

        // 休憩を更新
        $attendance->breaks()->delete();
        foreach ($validated['breaks'] ?? [] as $break) {
            if (!empty($break['start']) && !empty($break['end'])) {
                WorkBreak::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $break['start'],
                    'break_end' => $break['end'],
                ]);
            }
        }

        return redirect()->route('attendance.create');
    }

    // 管理者：日次一覧
    public function daily(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $date = Carbon::parse($date);

        $attendances = Attendance::with('user', 'breaks')
            ->where('date', $date)
            ->get();

        return view('attendance.daily', compact('attendances', 'date'));
    }

}
