<?php

namespace Brazidev\Brazidesk\Middleware;

use Closure;
use Brazidev\Brazidesk\Models\Agent;

class IsAdminMiddleware
{
    /**
     * Run the request filter.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Agent::isAdmin()) {
            return $next($request);
        }

        return redirect()->action('\Brazidev\Brazidesk\Controllers\TicketsController@index')
            ->with('warning', trans('brazidesk::lang.you-are-not-permitted-to-access'));
    }
}
