<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'memo',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // リレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(WorkBreak::class);
    }

    public function request()
    {
        return $this->hasOne(AttendanceRequest::class)->where('status', 'pending');
    }

    // 休憩時間計算
    public function getTotalBreakMinutes()
    {
        $total = 0;
        foreach ($this->breaks as $break) {
            if ($break->break_start && $break->break_end) {
                // 秒単位を切り捨てて分単位で計算
                $start = \Carbon\Carbon::parse($break->break_start)->setSecond(0);
                $end = \Carbon\Carbon::parse($break->break_end)->setSecond(0);
                $total += $start->diffInMinutes($end);
            }
        }
        return $total;
    }

    // 勤務時間計算
    public function getWorkHours()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return '0:00';
        }

        $start = \Carbon\Carbon::parse($this->clock_in)->setSecond(0);
        $end = \Carbon\Carbon::parse($this->clock_out)->setSecond(0);
        $workMinutes = $start->diffInMinutes($end) - $this->getTotalBreakMinutes();

        $hours = floor($workMinutes / 60);
        $minutes = $workMinutes % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }
}
