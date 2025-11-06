<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\User;
use App\Models\WorkBreak;
use App\Http\Requests\AttendanceEditRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RequestController extends Controller
{

    // 申請一覧
    public function index(Request $request)
    {
        $user = auth()->user();
        $page = $request->get('page', 'pending');

        if ( $user->role === 'user'){
            if ($page == 'approved' ){
                $requests = AttendanceRequest::with('user', 'attendance')
                    ->where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $requests = AttendanceRequest::with('user', 'attendance')
                    ->where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        } else {
            if ($page == 'approved' ){
                $requests = AttendanceRequest::with('user', 'attendance')
                    ->where('status', 'approved')
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $requests = AttendanceRequest::with('user', 'attendance')
                    ->where('status', 'pending')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        }

        return view('request.index', compact('requests', 'page'));
    }

    // 修正申請
    public function requestEdit(AttendanceEditRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $validated = $request->validated();

        AttendanceRequest::create([
            'user_id' => auth()->id(),
            'attendance_id' => $attendance->id,
            'requested_clock_in' => $validated['clock_in'],
            'requested_clock_out' => $validated['clock_out'],
            'requested_breaks' => $validated['breaks'] ?? [],
            'status' => 'pending',
            'memo' => $request->memo,
        ]);

        return redirect()->route('attendance.index');
    }

    // 申請詳細
    public function requestShow($id)
    {
        $request = AttendanceRequest::with('user', 'attendance.breaks')->findOrFail($id);
        return view('request.show', compact('request'));
    }

    // 申請承認
    public function approveRequest($id)
    {
        $attendanceRequest = AttendanceRequest::findOrFail($id);
        $attendance = $attendanceRequest->attendance;

        // 勤怠を更新
        $attendance->update([
            'clock_in' => $attendanceRequest->requested_clock_in,
            'clock_out' => $attendanceRequest->requested_clock_out,
            'memo' => $attendanceRequest->memo,
        ]);

        // 休憩を更新
        $attendance->breaks()->delete();
        foreach ($attendanceRequest->requested_breaks ?? [] as $break) {
            if (!empty($break['start']) && !empty($break['end'])) {
                WorkBreak::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $break['start'],
                    'break_end' => $break['end'],
                ]);
            }
        }

        // 申請を承認
        $attendanceRequest->update(['status' => 'approved']);

        return redirect()->route('request.index');
    }

}
