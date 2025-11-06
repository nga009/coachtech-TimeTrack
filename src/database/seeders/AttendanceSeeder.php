<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\WorkBreak;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
/*        // 管理者ユーザー作成
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);
*/
        // 一般ユーザー2名作成
        $user1 = User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => Carbon::now(),
            'role' => 'user',
        ]);

        $user2 = User::create([
            'name' => '佐藤花子',
            'email' => 'sato@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => Carbon::now(),
            'role' => 'user',
        ]);

        // 2025/10/01 から 2025/11/08 までの勤怠データ作成
        $startDate = Carbon::parse('2025-10-01');
        $endDate = Carbon::parse('2025-11-08');
        $users = [$user1, $user2];

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            foreach ($users as $user) {
                // 土日または10%の確率で休日
                $isWeekend = $currentDate->isWeekend();
                $isHoliday = rand(1, 100) <= 10;

                if ($isWeekend || $isHoliday) {
                    // 休日は勤怠データなし
                    continue;
                }

                // 出勤時間: 8:00-10:00のランダム
                $clockInHour = rand(8, 9);
                $clockInMinute = rand(0, 59);
                $clockIn = sprintf('%02d:%02d:00', $clockInHour, $clockInMinute);

                // 退勤時間: 出勤から8-10時間後
                $clockInTime = strtotime($clockIn);
                $workHours = rand(8, 10);
                $workMinutes = rand(0, 59);
                $clockOutTime = $clockInTime + ($workHours * 3600) + ($workMinutes * 60);
                $clockOut = date('H:i:s', $clockOutTime);

                // 勤怠データ作成
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $currentDate->format('Y-m-d'),
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                ]);

                // 休憩時間: 1-2回
                $breakCount = rand(1, 2);
                for ($i = 0; $i < $breakCount; $i++) {
                    if ($i === 0) {
                        // 1回目の休憩: 11:30-13:00の間に開始
                        $breakStartHour = rand(11, 12);
                        $breakStartMinute = rand(0, 59);
                    } else {
                        // 2回目の休憩: 15:00-16:00の間に開始
                        $breakStartHour = rand(15, 15);
                        $breakStartMinute = rand(0, 59);
                    }

                    $breakStart = sprintf('%02d:%02d:00', $breakStartHour, $breakStartMinute);
                    $breakStartTime = strtotime($breakStart);

                    // 休憩時間: 30分-1時間
                    $breakDuration = rand(30, 60);
                    $breakEndTime = $breakStartTime + ($breakDuration * 60);
                    $breakEnd = date('H:i:s', $breakEndTime);

                    // 休憩が退勤時刻を超えないようにチェック
                    if ($breakEndTime < $clockOutTime) {
                        WorkBreak::create([
                            'attendance_id' => $attendance->id,
                            'break_start' => $breakStart,
                            'break_end' => $breakEnd,
                        ]);
                    }
                }
            }
            $currentDate->addDay();
        }
    }
}