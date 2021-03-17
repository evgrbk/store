<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LogoutIfNotActive
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->guard('customers')->check() && !auth()->guard('customers')->user()->isActive()) {
            auth()->guard('customers')->logout();
            abort(403, 'Ваша учетная запись была заблокирована!');
        }
        return $next($request);
    }
}
