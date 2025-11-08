<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginValidationTest extends TestCase
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
        // テスト手順1: ログインページを開く
        $response = $this->get('/login');
        $response->assertStatus(200);

        // テスト手順2: メールアドレスを入力せずに他の必要項目を入力する
        $formData = [
            'email' => '', // メールアドレスを空にする
            'password' => 'password123',
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
        // テスト手順1: ログインページを開く
        $response = $this->get('/login');
        $response->assertStatus(200);

        // テスト手順2: パスワードを入力せずに他の必要項目を入力する
        $formData = [
            'email' => 'test@example.com',
            'password' => '', // パスワードを空にする
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
        // テスト手順1: ログインページを開く
        $response = $this->get('/login');
        $response->assertStatus(200);

        // テスト手順2: 登録されていない情報を入力する
        $formData = [
            'email' => 'nonexistent@example.com', // 存在しないメールアドレス
            'password' => 'wrongpassword',         // 間違ったパスワード
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
            'email' => 'test@example.com',
            'password' => Hash::make('correct_password'),
        ]);

        $response = $this->get('/login');
        $response->assertStatus(200);

        $formData = [
            'email' => 'test@example.com',
            'password' => 'wrong_password', // 間違ったパスワード
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
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // テスト手順1: ログインページを開く
        $response = $this->get('/login');
        $response->assertStatus(200);

        // テスト手順2: 全ての必要項目を入力する
        $formData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        // テスト手順3: ログインボタンを押す（POSTリクエスト送信）
        $response = $this->post('/login', $formData);

        // 期待挙動: ログイン処理が実行される
        $response->assertStatus(302);
        
        // 勤怠登録画面にアクセスできることを確認
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        
        // ユーザーがログインしていることを確認
        $this->assertAuthenticated();
        $this->assertEquals($user->id, auth()->id());
    }

    /**
     * 管理者ユーザーの場合のバリデーションテスト
     * 
     * @test
     */
    public function 管理者ユーザーの場合ログインできない()
    {
        // テスト手順の準備: 事前にユーザーを作成
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'role' => 'admin',  // 管理者
        ]);

        // テスト手順1: ログインページを開く
        $response = $this->get('/login');
        $response->assertStatus(200);

        // テスト手順2: 全ての必要項目を入力する
        $formData = [
            'email' => 'test@example.com',
            'password' => 'password123',
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
