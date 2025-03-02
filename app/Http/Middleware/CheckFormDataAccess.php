<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class CheckFormDataAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\Form $form */
        $form = $request->route()->parameter('form');

        if ($form?->require_auth && ! $request->user('sanctum')) {
            throw new UnauthorizedHttpException('Bearer', 'Unauthenticated');
        }

        return $next($request);
    }
}
