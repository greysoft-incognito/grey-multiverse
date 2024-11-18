<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user || ! $user->hasAnyRole(config('permission-defs.admin_roles'))) {
            return (new Controller())->buildResponse(
                [
                    'data' => UserResource::make($request->user()),
                    'status' => 'error',
                    'message' => 'You do not have permision to complete this action.',
                    'status_code' => 403,
                ],
                [
                    'response' => [],
                ]
            );
        }

        return $next($request);
    }
}
