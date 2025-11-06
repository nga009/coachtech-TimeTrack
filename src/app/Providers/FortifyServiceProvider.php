<?php

namespace App\Providers;

use Illuminate\Http\Request;    
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use App\Actions\Fortify\CreateNewUser;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use App\Http\Requests\LoginRequest;
use App\Models\User;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {

        $this->app->singleton(RegisterResponse::class, function () {
            return new class implements RegisterResponse {
                public function toResponse($request)
                {
                    // 登録直後、未認証ユーザーを必ず誘導画面へ
                    return redirect()->to('/email/verify');
                }
            };
        });

        // ログイン後リダイレクトの差し替え
        $this->app->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse {
                public function toResponse($request)
                {
                    $user = $request->user();

                    // メール未認証の場合はメール認証誘導画面へ遷移
                    if ($user && is_null($user->email_verified_at)) {
                        return view('auth.verify-notice');
                    }

                    // 既定の行き先
                    $default = $user?->role === 'admin'
                        ? '/admin/attendance/list'
                        : '/attendance';

                    // 「意図したURL(intended)」が権限に合うときだけ使う
                    $intended = $request->session()->pull('url.intended');
                    if ($intended) {
                        $path = parse_url($intended, PHP_URL_PATH) ?? '/';
                        $isRoot = ($path === '/' || $path === '' || $path === null);
                        $isAdminPath = str_starts_with($path, '/admin');

                        if (!$isRoot) {
                            if ($user->role === 'admin' && $isAdminPath)  return redirect()->to($intended);
                            if ($user->role === 'user'  && !$isAdminPath) return redirect()->to($intended);
                        }
                    }
                    return redirect()->to($default);
                }
            };
        });

        // ログアウト時にログイン画面の送信先判断
        $this->app->singleton(LogoutResponse::class, function () {
            return new class implements LogoutResponse {
                public function toResponse($request)
                {
                    $context = $request->input('context');

                    $ref = $request->headers->get('referer');
                    $path = $ref ? (parse_url($ref, PHP_URL_PATH) ?? '/') : '/';
                    $isAdminRef = str_starts_with($path, '/admin');

                    $toAdmin = ($context === 'admin') || ($context === null && $isAdminRef);

                    return redirect()->to($toAdmin ? route('admin.login') : route('login'));
                }
            };
        });
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        // ビューの設定
        // 会員登録画面
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // デフォルトのログイン画面（一般用）
        Fortify::loginView(function () {
            return view('auth.login', [
                'title' => 'ログイン',
                'loginType' => 'user',
            ]);
        });

        // メール認証待ち画面
        Fortify::verifyEmailView(function () {
            return view('auth.verify-notice');
        });

        // ID/パスワード一致に加えて role もチェック
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->input('email'))->first();
            if ($user && !Hash::check($request->password, $user->password)) {
                return null;
            }            

            // どちらのログイン画面から来たか
            $loginType = $request->input('login_type', 'user'); // hiddenで送る

            // 画面とユーザーroleが一致してなければ拒否
            if ($loginType === 'admin' && $user->role !== 'admin') {
                return null;
            }
            if ($loginType === 'user' && $user->role !== 'user') {
                return null;
            }

            return $user; // 認証成功
        });

        // バリデーション
        $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);

        // login処理の実行回数を1分あたり10回までに制限
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });
    }
}
