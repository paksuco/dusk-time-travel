<?php

namespace Paksuco\DuskTimeTravel;

use Illuminate\Foundation\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Paksuco\DuskTimeTravel\Middleware\ModifyDuskBrowserTime;

class DuskTimeTravelServiceProvider extends ServiceProvider
{
    public function boot(Router $router, Kernel $kernel)
    {
        $router->pushMiddlewareToGroup("web", ModifyDuskBrowserTime::class);
    }
}
