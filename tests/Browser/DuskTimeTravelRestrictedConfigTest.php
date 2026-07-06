<?php

namespace Paksuco\DuskTimeTravel\Tests\Browser;

use Illuminate\Support\Carbon;
use Paksuco\DuskTimeTravel\DuskTimeTravelServiceProvider;
use Paksuco\DuskTimeTravel\Tests\DuskTestCase;

class DuskTimeTravelRestrictedConfigTest extends DuskTestCase
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

        $app['config']->set('dusk-time-travel.middleware', ['web']);
    }

    public function testRestrictingToWebStillTravelsTimeOnWeb()
    {
        $target = Carbon::parse('2040-10-20 16:00:00');

        $response = $this->withUnencryptedCookie('dusk-skip-time', $target->toIso8601String())
            ->get('/web/time');

        $response->assertSee($target->toIso8601String());
    }

    public function testRestrictingToWebLeavesApiUntouched()
    {
        $target = Carbon::parse('2040-10-20 16:00:00');

        $response = $this->withUnencryptedCookie('dusk-skip-time', $target->toIso8601String())
            ->get('/api/time');

        $response->assertDontSee($target->toIso8601String());
    }

    /**
     * A typo'd or nonexistent group name in configuration must not break
     * the application — it must be silently skipped.
     */
    public function testRestrictingToWebSkipsNonExistingGroupNames()
    {
        $app = $this->app;
        $app['config']->set('dusk-time-travel.middleware', ['web', 'does-not-exist']);

        (new DuskTimeTravelServiceProvider($app))->boot(
            $app['router'],
            $app->make(\Illuminate\Contracts\Http\Kernel::class)
        );

        $target = Carbon::parse('2040-10-20 16:00:00');

        $response = $this->withUnencryptedCookie('dusk-skip-time', $target->toIso8601String())
            ->get('/web/time');

        $response->assertSee($target->toIso8601String());
    }
}
