<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\WorkBreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2025-11-08 09:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * 現在の日時情報がUIと同じ形式で出力されている
     * 
     * @test
     */
    public function 現在の日時情報がUIと同じ形式で出力されている()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('2025年11月8日(土)');
        $response->assertSee('09:00');
    }

    /**
     * 勤務外の場合勤怠ステータスが正しく表示される
     * 
     * @test
     */
    public function 勤務外の場合勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            '勤務外',
            '2025年11月8日(土)'
        ]);
    }
    
    /**
     * 出勤中の場合勤怠ステータスが正しく表示される
     * 
     * @test
     */
    public function 出勤中の場合勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();
        
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            '出勤中',
            '2025年11月8日(土)'
        ]);
    }
    
    /**
     * 休憩中の場合勤怠ステータスが正しく表示される
     * 
     * @test
     */
    public function 休憩中の場合勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();
        
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        WorkBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            '休憩中',
            '2025年11月8日(土)'
        ]);
    }

    /**
     * 退勤済の場合勤怠ステータスが正しく表示される
     * 
     * @test
     */
    public function 退勤済の場合勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();
        
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            '退勤済',
            '2025年11月8日(土)'
        ]);
    }

    /**
     * 出勤ボタンが正しく機能する
     * 
     * @test
     */
    public function 出勤ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/clock-in');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => '2025-11-08',
            'clock_in' => '09:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    /**
     * 出勤は一日一回のみできる
     * 
     * @test
     */
    public function 出勤は一日一回のみできる()
    {
        $user = User::factory()->create();
        
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
        $response->assertSee('お疲れ様でした。');
        $response->assertDontSee('<button type="submit" class="attend-btn">出勤</button>', false);

    }

    /**
     * 出勤時刻が勤怠一覧画面で確認できる
     * 
     * @test
     */
    public function 出勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/clock-in');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            '11/08(土)',
            '09:00'
        ]);
    }

    /**
     * 休憩ボタンが正しく機能する
     * 
     * @test
     */
    public function 休憩ボタンが正しく機能する()
    {
        $user = User::factory()->create();
        
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->post('/break-start');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
            'break_start' => '09:00:00',
            'break_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');
    }

    /**
     * 休憩は一日に何回でもできる
     * 
     * @test
     */
    public function 休憩は一日に何回でもできる()
    {
        $user = User::factory()->create();
        
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        // 1回目の休憩
        $this->actingAs($user)->post('/break-start');
        Carbon::setTestNow('2025-11-08 10:00:00');
        $this->actingAs($user)->post('/break-end');

        // 2回目の休憩入ボタンが表示されることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<button type="submit" class="break-btn">休憩入</button>', false);
        $response->assertSee('出勤中');

        // 2回目の休憩
        Carbon::setTestNow('2025-11-08 14:00:00');
        $this->actingAs($user)->post('/break-start');

        // 2回目の休憩が記録されることを確認
        $this->assertEquals(2, $attendance->breaks()->count());
    }

    /**
     * 休憩戻ボタンが正しく機能する
     * 
     * @test
     */
    public function 休憩戻ボタンが正しく機能する()
    {
        $user = User::factory()->create();
        
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $break = WorkBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => null,
        ]);

        Carbon::setTestNow('2025-11-08 13:00:00');
        $response = $this->actingAs($user)->post('/break-end');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('breaks', [
            'id' => $break->id,
            'break_end' => '13:00:00',
        ]);

        // 画面でステータスが「出勤中」になることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    /**
     * 休憩戻は一日に何回でもできる
     * 
     * @test
     */
    public function 休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->create();
        
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        // 1回目の休憩と休憩戻
        $this->actingAs($user)->post('/break-start');
        Carbon::setTestNow('2025-11-08 10:00:00');
        $this->actingAs($user)->post('/break-end');

        // 2回目の休憩
        Carbon::setTestNow('2025-11-08 14:00:00');
        $this->actingAs($user)->post('/break-start');

        // 2回目の休憩戻ボタンが表示されることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<button type="submit" class="break-btn">休憩戻</button>', false);
        $response->assertSee('休憩中');

        // 2回目の休憩戻
        Carbon::setTestNow('2025-11-08 15:00:00');
        $this->actingAs($user)->post('/break-end');

        // 2回とも休憩終了時刻が記録されていることを確認
        $this->assertEquals(2, $attendance->breaks()->whereNotNull('break_end')->count());
    }

    /**
     * 休憩時刻が勤怠一覧画面で確認できる
     * 
     * @test
     */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();
        
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $this->actingAs($user)->post('/break-start');
        Carbon::setTestNow('2025-11-08 13:00:00');
        $this->actingAs($user)->post('/break-end');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        // 休憩時間が4時間（240分）と表示される
        $response->assertSeeInOrder([
            '11/08(土)',
            '09:00',
            '4:00'
        ]);
    }

    /**
     * 退勤ボタンが正しく機能する
     * 
     * @test
     */
    public function 退勤ボタンが正しく機能する()
    {
        $user = User::factory()->create();
        
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        Carbon::setTestNow('2025-11-08 18:00:00');
        $response = $this->actingAs($user)->post('/clock-out');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_out' => '18:00:00',
        ]);

        // 画面でステータスが「退勤済」になることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');
    }

    /**
     * 退勤時刻が勤怠一覧画面で確認できる
     * 
     * @test
     */
    public function 退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/clock-in');
        Carbon::setTestNow('2025-11-08 18:00:00');
        $this->actingAs($user)->post('/clock-out');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            '11/08(土)',
            '09:00',
            '18:00'
        ]);
    }
}
