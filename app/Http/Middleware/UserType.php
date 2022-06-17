<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class UserType
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @param $type
     * @return JsonResponse|RedirectResponse
     */
    public function handle(Request $request, Closure $next, $type)
    {
        if (User::TYPE_SLUGS[auth()->user()->type] !== $type) {
            return response()->json([
                'status' => trans('access.denied')
            ], JsonResponse::HTTP_FORBIDDEN);
        }
        return $next($request);
    }
}
