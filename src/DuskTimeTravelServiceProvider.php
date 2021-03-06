<?php

namespace Paksuco\DuskTimeTravel;

use Illuminate\Foundation\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Paksuco\DuskTimeTravel\Middleware\ModifyDuskBrowserTime;

class DuskTimeTravelServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(Router $router, Kernel $kernel)
    {
        $router->prependMiddlewareToGroup("web", ModifyDuskBrowserTime::class);
    }
}
