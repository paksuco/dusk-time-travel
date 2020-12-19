<?php

namespace Paksuco\DuskTimeTravel\Tests;

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
            DuskTimeTravelServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $kernel = app('Illuminate\Contracts\Http\Kernel');

        $kernel->pushMiddleware(ModifyDuskBrowserTime::class);
    }
}
