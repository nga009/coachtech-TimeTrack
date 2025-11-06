<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        $clockIn = $this->faker->time('H:i:s', '10:00:00');
        $clockInTime = strtotime($clockIn);
        $clockOutTime = $clockInTime + rand(28800, 36000); // 8-10時間後
        $clockOut = date('H:i:s', $clockOutTime);

        return [
            'user_id' => User::factory(),
            'date' => $this->faker->date(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ];
    }

    /**
     * 休日（出勤なし）の状態
     */
    public function holiday()
    {
        return $this->state(function (array $attributes) {
            return [
                'clock_in' => null,
                'clock_out' => null,
            ];
        });
    }
}