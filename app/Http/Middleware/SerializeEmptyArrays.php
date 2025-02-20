<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SerializeEmptyArrays
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->merge($this->convertEmptyStringsToArrays($request->all()));
        return $next($request);
    }

    private function convertEmptyStringsToArrays(array $data): array
    {
        return array_map(function ($value) {
            if (is_string($value) && $value === '[]') {
                return [];
            } elseif (is_array($value)) {
                return $this->convertEmptyStringsToArrays($value); // Recursively handle nested arrays
            }
            return $value;
        }, $data);
    }
}
