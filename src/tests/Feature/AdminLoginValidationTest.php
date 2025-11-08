<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminLoginValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレスが入力されていない場合のバリデーションテスト
     * 
     * テスト内容：メールアドレスが入力されていない場合、バリデーションメッセージが表示される
     * 
     * @test
     */
    public function メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        // テスト手順1: 管理者ログインページを開く
        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        // テスト手順2: メールアドレスを入力せずに他の必要項目を入力する
        $formData = [
            'email' => '', // メールアドレスを空にする
            'password' => 'password123',
            'login_type' => 'admin',
        ];

        // テスト手順3: ログインボタンを押す（POSTリクエスト送信）
        $response = $this->post('/login', $formData);

        // 期待挙動: バリデーションエラーでリダイレクトされる
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);

        // 期待挙動: 「メールアドレスを入力してください」というメッセージが表示される
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);

        // ユーザーがログインしていないことを確認
        $this->assertGuest();
    }

    /**
     * パスワードが入力されていない場合のバリデーションテスト
     * 
     * テスト内容：パスワードが入力されていない場合、バリデーションメッセージが表示される
     * 
     * @test
     */
    public function パスワードが未入力の場合バリデーションメッセージが表示される()
    {
        // テスト手順1: 管理者ログインページを開く
        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        // テスト手順2: パスワードを入力せずに他の必要項目を入力する
        $formData = [
            'email' => 'test@example.com',
            'password' => '', // パスワードを空にする
            'login_type' => 'admin',
        ];

        // テスト手順3: ログインボタンを押す（POSTリクエスト送信）
        $response = $this->post('/login', $formData);

        // 期待挙動: 「パスワードを入力してください」というメッセージが表示される
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);

        // ユーザーがログインしていないことを確認
        $this->assertGuest();
    }

    /**
     * 入力情報が間違っている場合のバリデーションテスト
     * 
     * テスト内容：入力情報が間違っている場合、バリデーションメッセージが表示される
     * 
     * @test
     */
    public function 入力情報が間違っている場合バリデーションメッセージが表示される()
    {
        // テスト手順1: 管理者ログインページを開く
        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        // テスト手順2: 登録されていない情報を入力する
        $formData = [
            'email' => 'nonexistent@example.com', // 存在しないメールアドレス
            'password' => 'wrongpassword',         // 間違ったパスワード
            'login_type' => 'admin',
        ];

        // テスト手順3: ログインボタンを押す（POSTリクエスト送信）
        $response = $this->post('/login', $formData);

        // 期待挙動: 「ログイン情報が登録されていません」というメッセージが表示される
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);

        // ユーザーがログインしていないことを確認
        $this->assertGuest();
    }

    /**
     * 存在するユーザーで間違ったパスワードの場合のバリデーションテスト
     * 
     * @test
     */
    public function 存在するユーザーで間違ったパスワードの場合バリデーションメッセージが表示される()
    {
        // 事前にユーザーを作成
        $user = User::factory()->create([
            'email' => 'admintest@example.com',
            'password' => Hash::make('correct_password'),
            'role' => 'admin',
        ]);

        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        $formData = [
            'email' => 'admintest@example.com',
            'password' => 'wrong_password', // 間違ったパスワード
            'login_type' => 'admin',
        ];

        $response = $this->post('/login', $formData);

        // 期待挙動: 「ログイン情報が登録されていません」というメッセージが表示される
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);

        // ユーザーがログインしていないことを確認
        $this->assertGuest();
    }

    /**
     * 正しい情報が入力された場合のログインテスト
     * 
     * テスト内容：正しい情報が入力された場合、ログイン処理が実行される
     * 
     * @test
     */
    public function 正しい情報が入力された場合ログイン処理が実行される()
    {
        // テスト手順の準備: 事前にユーザーを作成
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'admintest@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        // テスト手順1: ログインページを開く
        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        // テスト手順2: 全ての必要項目を入力する
        $formData = [
            'email' => 'admintest@example.com',
            'password' => 'password123',
            'login_type' => 'admin',
        ];

        // テスト手順3: ログインボタンを押す（POSTリクエスト送信）
        $response = $this->post('/login', $formData);

        // 期待挙動: ログイン処理が実行される
        $response->assertStatus(302);
        
        // 勤怠登録画面にアクセスできることを確認
        $response = $this->get('/admin/attendance/list');
        $response->assertStatus(200);
        
        // ユーザーがログインしていることを確認
        $this->assertAuthenticated();
        $this->assertEquals($user->id, auth()->id());
    }

    /**
     * 一般ユーザーの場合のバリデーションテスト
     * 
     * @test
     */
    public function 一般ユーザーの場合ログインできない()
    {
        // テスト手順の準備: 事前にユーザーを作成
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'role' => 'user',  // 一般ユーザー
        ]);

        // テスト手順1: ログインページを開く
        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        // テスト手順2: 全ての必要項目を入力する
        $formData = [
            'email' => 'user@example.com',
            'password' => 'password123',
            'login_type' => 'admin',
        ];

        // テスト手順3: ログインボタンを押す（POSTリクエスト送信）
        $response = $this->post('/login', $formData);

        // 期待挙動: 「ログイン情報が登録されていません」というメッセージが表示される
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);

        // ユーザーがログインしていないことを確認
        $this->assertGuest();
    }
}
