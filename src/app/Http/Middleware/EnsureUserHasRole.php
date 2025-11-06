<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->guest(
                str_starts_with($request->path(), 'admin') ? route('admin.login') : route('login')
            );
        }

        if ($user->role !== $role) {
            // UX重視：適切なホームへ戻す。厳格にするなら abort(403) でもOK。
            if ($user->role === 'admin') {
                return redirect()->to('/admin/attendance/list');
            }
            return redirect()->to('/attendance');
        }

        return $next($request);
    }
}
