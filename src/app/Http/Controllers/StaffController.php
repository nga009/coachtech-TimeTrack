<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\User;
use App\Models\WorkBreak;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffController extends Controller
{

    // スタッフ一覧
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $date = Carbon::parse($date);

        $users = User::where('role', 'user')
            ->get();

        return view('staff.index', compact('users'));
    }

    // スタッフ別月次勤怠一覧
    public function monthly(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
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

        return view('attendance.index', compact('dates', 'year', 'month', 'user'));
    }

    // CSV出力
    public function export(Request $request, $id)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get()
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        $csvData = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            $attendance = $attendances->get($dateStr);
            $csvData[] = [
                'date' => $current->copy()->isoFormat('YYYY/MM/DD'),
                'clock_in' => $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '',
                'clock_out' => $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '',
                'break' => $attendance ? floor($attendance->getTotalBreakMinutes() / 60) . ':' . str_pad($attendance->getTotalBreakMinutes() % 60, 2, '0', STR_PAD_LEFT) : '',
                'total' => $attendance ? $attendance->getWorkHours() : '',
            ];
            $current->addDay();
        }

        $csvHeader = [
            '日付', '出勤', '退勤', '休憩', '合計'
        ];

        $response = new StreamedResponse(function () use ($csvHeader, $csvData) {
            $createCsvFile = fopen('php://output', 'w');

            mb_convert_variables('SJIS-win', 'UTF-8', $csvHeader);

            fputcsv($createCsvFile, $csvHeader);

            foreach ($csvData as $csv) {
                fputcsv($createCsvFile, $csv);
            }

            fclose($createCsvFile);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="勤怠一覧.csv"',
        ]);

        return $response;
    }

}
