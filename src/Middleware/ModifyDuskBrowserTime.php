<?php

namespace Paksuco\DuskTimeTravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

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
        ;
        if (Cookie::has("dusk-skip-time")) {
            /** @var string */
            $time = Cookie::get("dusk-skip-time");
            $parsed = Carbon::parse($time);
            if ($parsed->isValid()) {
                Carbon::setTestNow($parsed->toIso8601String());
            }
        }

        return $next($request);
    }
}
