<?php

namespace Brazidev\Brazidesk\Middleware;

use Closure;
use Brazidev\Brazidesk\Models\Agent;
use Brazidev\Brazidesk\Models\Setting;

class ResAccessMiddleware
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

        // All Agents have access in none restricted mode
        if (Setting::grab('agent_restrict') == 'no') {
            if (Agent::isAgent()) {
                return $next($request);
            }
        }

        $laravel_version_51 = version_compare(app()->version(), '5.2.0', '<');

        // if this is a ticket show page
        if ($request->route()->getName() == Setting::grab('main_route').'.show') {
            if ($laravel_version_51) {
                $ticket_id = $request->route(Setting::grab('main_route'));
            } else {
                $ticket_id = $request->route('ticket');
            }
        }

        // if this is a new comment on a ticket
        if ($request->route()->getName() == Setting::grab('main_route').'-comment.store') {
            $ticket_id = $request->get('ticket_id');
        }

        // Assigned Agent has access in the restricted mode enabled
        if (Agent::isAgent() && Agent::isAssignedAgent($ticket_id)) {
            return $next($request);
        }

        // Ticket Owner has access
        if (Agent::isTicketOwner($ticket_id)) {
            return $next($request);
        }

        return redirect()->action('\Brazidev\Brazidesk\Controllers\TicketsController@index')
            ->with('warning', trans('brazidesk::lang.you-are-not-permitted-to-access'));
    }
}
