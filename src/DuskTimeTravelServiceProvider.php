<?php

namespace Paksuco\DuskTimeTravel;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Paksuco\DuskTimeTravel\Middleware\ModifyDuskBrowserTime;

class DuskTimeTravelServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/dusk-time-travel.php', 'dusk-time-travel');
    }

    /**
     * @return void
     */
    public function boot(Router $router, Kernel $kernel)
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/dusk-time-travel.php' => $this->app->configPath('dusk-time-travel.php'),
            ], 'dusk-time-travel-config');
        }

        $middleware = config('dusk-time-travel.middleware', true);

        // Do not register middleware
        if ($middleware === false) {
            return;
        }

        // We require this function, which was added in Laravel 8
        // This means we can't do selective routing groups using an array
        // Fall back safely to the default if it is not found
        if (! method_exists($router, 'hasMiddlewareGroup')) {
            $middleware = true;
        }

        // Register middleware globally (all groups)
        if ($middleware === true) {
            // prependMiddleware() is declared on the concrete Foundation kernel,
            // not the Contracts kernel, so narrow the type before calling it.
            // We do this to keep Psalm happy.
            if ($kernel instanceof HttpKernel) {
                $kernel->prependMiddleware(ModifyDuskBrowserTime::class);
            }

            return;
        }

        // Register middleware selectively (only for the specified groups)
        foreach ((array) $middleware as $group) {
            // If the named group does not exist, we silently skip it
            if ($router->hasMiddlewareGroup($group)) {
                $router->prependMiddlewareToGroup($group, ModifyDuskBrowserTime::class);
            }
        }
    }
}
