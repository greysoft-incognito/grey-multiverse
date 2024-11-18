<?php

namespace V1\Http\Middleware;

use Closure;
use V1\Http\Controllers\Controller;
use V1\Traits\Permissions;

class IsAdmin
{
    use Permissions;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $redirectToRoute
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|null
     */
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        if (! $this->setPermissionsUser($request->user())->hasPermission('admin')) {
            return (new Controller)->buildResponse([
                'message' => 'You do not have permision to view this page.',
                'status' => 'error',
                'status_code' => 403,
            ]);
        }

        return $next($request);
    }
}
