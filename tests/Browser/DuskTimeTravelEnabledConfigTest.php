<?php

namespace Paksuco\DuskTimeTravel\Tests\Browser;

use Illuminate\Support\Carbon;
use Paksuco\DuskTimeTravel\DuskTimeTravelServiceProvider;
use Paksuco\DuskTimeTravel\Tests\DuskTestCase;

class DuskTimeTravelEnabledConfigTest extends DuskTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            DuskTimeTravelServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('dusk-time-travel.middleware', true);
    }

    public function testTrueConfigWorksOnWebRoutes()
    {
        $target = Carbon::parse('2040-10-20 16:00:00');

        $response = $this->withUnencryptedCookie('dusk-skip-time', $target->toIso8601String())
            ->get('/web/time');

        $response->assertSee($target->toIso8601String());
    }

    public function testTrueConfigWorksOnApiRoutes()
    {
        $target = Carbon::parse('2040-10-20 16:00:00');

        $response = $this->withUnencryptedCookie('dusk-skip-time', $target->toIso8601String())
            ->get('/api/time');

        $response->assertSee($target->toIso8601String());
    }

    public function testTrueConfigWorksOnJsRoutes()
    {
        $target = Carbon::parse('2040-10-20 16:00:00');

        $response = $this->withUnencryptedCookie('dusk-skip-time', $target->toIso8601String())
            ->get('/js/time');

        $response->assertSee($target->toIso8601String());
    }

    public function testTrueConfigWorksOnUngroupedRoutes()
    {
        $target = Carbon::parse('2040-10-20 16:00:00');

        $response = $this->withUnencryptedCookie('dusk-skip-time', $target->toIso8601String())
            ->get('/ungrouped/time');

        $response->assertSee($target->toIso8601String());
    }

    /**
     * Applications without an "api" route group (e.g. a fresh Laravel 11+
     * skeleton that never ran `php artisan install:api`) are unaffected by
     * default: the global registration doesn't need that group to exist,
     * so nothing breaks and nothing needs to be configured.
     */
    public function testTrueConfigDoesNotRequireApiRouteToExist()
    {
        $router = $this->app['router'];

        if (! method_exists($router, 'hasMiddlewareGroup') || ! method_exists($router, 'flushMiddlewareGroups')) {
            // Router::hasMiddlewareGroup()/flushMiddlewareGroups() requires Laravel 8+
            $this->markTestSkipped('This test is skipped in Laravel 7.x');
        }

        $this->assertTrue($router->hasMiddlewareGroup('api'));

        // Simulate an application that never registered one at all.
        $router->flushMiddlewareGroups();
        $router->middlewareGroup('web', []);

        $this->assertFalse($router->hasMiddlewareGroup('api'));

        $target = Carbon::parse('2040-10-20 16:00:00');
        $response = $this->withUnencryptedCookie('dusk-skip-time', $target->toIso8601String())
            ->get('/ungrouped/time');

        $response->assertSee($target->toIso8601String());
    }
}
