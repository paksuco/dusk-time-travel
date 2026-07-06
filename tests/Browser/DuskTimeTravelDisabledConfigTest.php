<?php

namespace Paksuco\DuskTimeTravel\Tests\Browser;

use Illuminate\Support\Carbon;
use Paksuco\DuskTimeTravel\DuskTimeTravelServiceProvider;
use Paksuco\DuskTimeTravel\Tests\DuskTestCase;

class DuskTimeTravelDisabledConfigTest extends DuskTestCase
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

        $app['config']->set('dusk-time-travel.middleware', false);
    }

    public function testFalseConfigDisablesTimeTravelEntirely()
    {
        $target = Carbon::parse('2040-10-20 16:00:00');

        $response = $this
            ->withUnencryptedCookie('dusk-skip-time', $target->toIso8601String())
            ->get('/web/time');

        $response->assertDontSee($target->toIso8601String());
    }
}
