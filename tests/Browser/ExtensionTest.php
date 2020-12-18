<?php

namespace Paksuco\DuskTimeTravel\Tests\Browser;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Paksuco\DuskTimeTravel\Browser;
use Paksuco\DuskTimeTravel\Tests\DuskTestCase;

class ExtensionTest extends DuskTestCase
{
    /** @test */
    public function testTimeTravelWorking()
    {
        $this->mockViewRoutes();

        $this->browse(function (Browser $browser) {
            $browser
                ->visitRoute("paksuco/time/travel")
                ->assertSee(Carbon::now()->startOfHour()->toIso8601String())
                ->travelTo(Carbon::yesterday()->setHour(4))
                ->refresh()
                ->assertSee(Carbon::yesterday()->setHour(4)->startOfHour()->toIso8601String())
                ->travelBack()
                ->refresh()
                ->assertSee(Carbon::now()->startOfHour()->toIso8601String());
        });
    }

    private function mockViewRoutes()
    {
        Route::get('paksuco/time/travel', function () {
            echo Carbon::now()->startOfHour()->toIso8601String();
        });
    }
}
