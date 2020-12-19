<?php

namespace Paksuco\DuskTimeTravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

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
    {;
        if (Cookie::has("dusk-skip-time")) {
            $time = Cookie::get("dusk-skip-time");
            Carbon::setTestNow(new Carbon($time));
        }

        return $next($request);
    }
}
