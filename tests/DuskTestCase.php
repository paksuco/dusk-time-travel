<?php

namespace Paksuco\DuskTimeTravel\Tests;

use Illuminate\Support\Carbon;
use Orchestra\Testbench\Dusk\TestCase as BaseTestCase;
use Paksuco\DuskTimeTravel\Browser as TimeTravelEnabledBrowser;
use Paksuco\DuskTimeTravel\DuskTimeTravelServiceProvider;
use Paksuco\DuskTimeTravel\Middleware\ModifyDuskBrowserTime;

abstract class DuskTestCase extends BaseTestCase
{
    protected static $baseServeHost = 'localhost';
    protected static $baseServePort = 9516;

    /**
     * Create a new Browser instance.
     *
     * @param  \Facebook\WebDriver\Remote\RemoteWebDriver  $driver
     * @return \Laravel\Dusk\Browser
     */
    protected function newBrowser($driver)
    {
        return new TimeTravelEnabledBrowser($driver);
    }

    protected function getPackageProviders($app)
    {
        return [
            DuskTimeTravelServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->pushMiddleware();
        $this->registerTestRoute($app);
    }

    protected function pushMiddleware()
    {
        $kernel = $this->getKernel();
        $kernel->pushMiddleware(ModifyDuskBrowserTime::class);
    }

    protected function getKernel()
    {
        return app('Illuminate\Contracts\Http\Kernel');
    }

    protected function registerTestRoute($app)
    {
        $router = $app["router"];

        $router->get('time', [
            'middleware' => 'web',
            'uses' => function () {
                return Carbon::now()->startOfHour()->toIso8601String();
            },
        ]);

        $router->get('js-time', [
            'middleware' => 'web',
            'uses' => function () {
                // The inline script runs at document parse time, before any
                // post-load injection could execute, so these values prove
                // the Page.addScriptToEvaluateOnNewDocument path.
                return '
                <html>
                    <body>
                        <div id="js-iso"></div>
                        <div id="js-now"></div>
                        <div id="js-fn"></div>
                        <div id="js-explicit"></div>
                        <script>
                            document.getElementById("js-iso").textContent = new Date().toISOString();
                            document.getElementById("js-now").textContent = String(Date.now());
                            document.getElementById("js-fn").textContent = Date();
                            document.getElementById("js-explicit").textContent = new Date(2020, 0, 1, 12).toISOString();
                        </script>
                        <div id="server-time">' . Carbon::now()->toIso8601String() . '</div>
                    </body>
                </html>';
            },
        ]);
    }
}
