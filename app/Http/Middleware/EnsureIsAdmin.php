<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->isAdmin()) {
            abort(403, 'ليس لديك صلاحية الوصول إلى لوحة التحكم.');
        }

        if ($permission !== null && ! $user->can($permission)) {
            abort(403, 'ليس لديك صلاحية لأداء هذه العملية.');
        }

        return $next($request);
    }
}
