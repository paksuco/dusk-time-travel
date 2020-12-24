<?php

namespace Paksuco\DuskTimeTravel\Tests\Browser;

use Illuminate\Support\Carbon;
use Paksuco\DuskTimeTravel\Browser;
use Paksuco\DuskTimeTravel\Tests\DuskTestCase;

class ExtensionTest extends DuskTestCase
{
    /**
     * Define environment setup.
     *
     * @param  Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $app['router']->get(
            'time',
            [
                'middleware' => 'web',
                'uses' => function () {
                    return Carbon::now()->startOfHour()->toIso8601String();
                },
            ]
        );
    }

    public function testTimeRouteWorking()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit("/time")
                ->assertSee(Carbon::now()->startOfHour()->toIso8601String());
        });
    }

    /** @test */
    public function testTimeTravelWorking()
    {
        $this->browse(function (Browser $browser) {
            $browser->travelTo(Carbon::yesterday()->setHour(4))
                ->visit("/time")
                ->assertSee(Carbon::yesterday()->setHour(4)->startOfHour()->toIso8601String());
        });
    }

    public function testTimeTravelResetWorking()
    {
        $this->browse(function (Browser $browser) {
            $browser->travelTo(Carbon::yesterday()->setHour(4))
                ->visit("/time")
                ->assertSee(Carbon::yesterday()->setHour(4)->startOfHour()->toIso8601String());

            $browser->travelBack()
                ->visit("/time")
                ->assertSee(Carbon::now()->startOfHour()->toIso8601String());
        });
    }
}
