<?php

namespace Database\Factories;

use App\Models\WorkBreak;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakFactory extends Factory
{
    protected $model = WorkBreak::class;

    public function definition()
    {
        $breakStart = $this->faker->time('H:i:s', '13:00:00');
        $breakStartTime = strtotime($breakStart);
        $breakEndTime = $breakStartTime + rand(3600, 5400); // 1-1.5時間後
        $breakEnd = date('H:i:s', $breakEndTime);

        return [
            'attendance_id' => Attendance::factory(),
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
        ];
    }
}