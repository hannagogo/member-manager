<?php

namespace App\Http\Middleware;

use App\Services\PermissionResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMemberHasPermission
{
    public function handle(
        Request $request,
        Closure $next,
        string $permission,
        PermissionResolver $resolver
    ): Response {

        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (! $resolver->has($user, $permission)) {
            abort(403, 'Permission denied.');
        }

        return $next($request);
    }
}
