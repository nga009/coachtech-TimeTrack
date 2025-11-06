<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // リレーション
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceRequests()
    {
        return $this->hasMany(AttendanceRequest::class);
    }

    /**
     * 今日の勤怠データを取得
     */
    public function getTodayAttendance()
    {
        return $this->attendances()
            ->where('date', Carbon::today())
            ->first();
    }

    /**
     * 現在の勤怠状況を取得
     * @return string 'not_working'|'working'|'on_break'|'finished'
     */
    public function getAttendanceStatus()
    {
        $attendance = $this->getTodayAttendance();

        // 今日の勤怠データがない、または出勤していない
        if (!$attendance || !$attendance->clock_in) {
            return 'not_working';
        }

        // 退勤済み
        if ($attendance->clock_out) {
            return 'finished';
        }

        // 未完了の休憩があるか確認
        $currentBreak = $attendance->breaks()
            ->whereNull('break_end')
            ->first();

        // 休憩中
        if ($currentBreak) {
            return 'on_break';
        }

        // 出勤中
        return 'working';
    }

}
