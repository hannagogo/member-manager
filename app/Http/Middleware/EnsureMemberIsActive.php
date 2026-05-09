<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $account = $user->memberAccounts()
            ->where('status', 'active')
            ->first();

        if (! $account) {
            abort(403, 'No active member account.');
        }

        if (! $account->member) {
            abort(403, 'No linked member.');
        }

        if ($account->member->status !== 'active') {
            abort(403, 'Member inactive.');
        }

        return $next($request);
    }
}
