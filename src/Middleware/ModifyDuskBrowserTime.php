<?php

namespace Paksuco\DuskTimeTravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ModifyDuskBrowserTime
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (isset($_COOKIE["dusk-skip-time"])) {
            $time = $_COOKIE["dusk-skip-time"];
            Carbon::setTestNow(new Carbon($time));
        }
        return $next($request);
    }
}
