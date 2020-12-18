<?php

namespace Paksuco\DuskTimeTravel;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Paksuco\DuskTimeTravel\Middleware\ModifyDuskBrowserTime;

class DuskTimeTravelServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /** @var Router $router */
        $router = app("router");
        $router->pushMiddlewareToGroup("web", ModifyDuskBrowserTime::class);
    }
}
