<?php

namespace Paksuco\DuskTimeTravel\Tests\Browser;

use Illuminate\Support\Carbon;
use Paksuco\DuskTimeTravel\Browser;
use Paksuco\DuskTimeTravel\Tests\DuskTestCase;

class BrowserTimeTest extends DuskTestCase
{
    public function testJavaScriptSeesTraveledTimeAfterVisit()
    {
        $target = Carbon::parse('2021-03-04 05:06:07');

        $this->browse(function (Browser $browser) use ($target) {
            // travelTo() sets a cookie, which the browser only accepts while a
            // page of the app is open — a fresh session starts on about:blank,
            // so every test visits a page before its first travelTo().
            $browser->visit('/js-time')
                ->travelTo($target)->visit('/js-time');

            $this->assertCarbonWithin($target, $browser->text('#js-iso'));

            $jsNowSeconds = (int) round(((float) $browser->text('#js-now')) / 1000);
            $this->assertLessThan(15, abs($jsNowSeconds - $target->getTimestamp()));

            $browser->travelBack();
        });
    }

    public function testServerAndBrowserAgree()
    {
        $target = Carbon::parse('2021-03-04 05:06:07');

        $this->browse(function (Browser $browser) use ($target) {
            $browser->visit('/js-time')
                ->travelTo($target)->visit('/js-time');

            $serverTime = Carbon::parse($browser->text('#server-time'));
            $this->assertCarbonWithin($serverTime, $browser->text('#js-iso'));

            $browser->travelBack();
        });
    }

    public function testExplicitDateArgumentsUnaffected()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/js-time')
                ->travelTo(Carbon::parse('2031-06-07 08:09:10'))->visit('/js-time');

            $this->assertStringContainsString('2020-01-0', $browser->text('#js-explicit'));

            $year = $browser->script('return new Date(2020, 0, 1).getFullYear();')[0];
            $this->assertSame(2020, (int) $year);

            $browser->travelBack();
        });
    }

    public function testCurrentPageKeepsRealTimeUntilNextNavigation()
    {
        $target = Carbon::parse('2021-03-04 05:06:07');

        $this->browse(function (Browser $browser) use ($target) {
            $browser->visit('/js-time')->travelTo($target);

            // The current page is unaffected, consistent with server-side time.
            $jsNowSeconds = (int) round(((float) $browser->script('return Date.now();')[0]) / 1000);
            $this->assertLessThan(15, abs($jsNowSeconds - Carbon::now()->getTimestamp()));

            // The next page load uses the traveled time.
            $browser->visit('/js-time');
            $this->assertCarbonWithin($target, $browser->text('#js-iso'));

            $browser->travelBack();
        });
    }

    public function testTravelBackRestoresBrowserTime()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/js-time')
                ->travelTo(Carbon::parse('2021-03-04 05:06:07'))->visit('/js-time');

            $browser->travelBack()->visit('/js-time');

            $this->assertCarbonWithin(Carbon::now(), $browser->text('#js-iso'));
            $this->assertSame('undefined', $browser->script('return typeof window.__duskTimeTravel;')[0]);
        });
    }

    public function testDateFunctionCallReturnsString()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/js-time')
                ->travelTo(Carbon::parse('2021-03-04 05:06:07'))->visit('/js-time');

            $this->assertStringContainsString('2021', $browser->text('#js-fn'));

            $browser->travelBack();
        });
    }

    /**
     * Asserts a parseable time string is within tolerance of the expected time.
     *
     * The JavaScript clock ticks forward from the traveled instant, so
     * assertions allow for page age and CI slowness.
     *
     * @param   Carbon  $expected  The expected time
     * @param   string  $actual    The time string extracted from the page
     * @param   int     $seconds   The allowed difference in seconds
     *
     * @return  void
     */
    protected function assertCarbonWithin(Carbon $expected, $actual, $seconds = 15)
    {
        $difference = abs(Carbon::parse($actual)->getTimestamp() - $expected->getTimestamp());

        $this->assertLessThan(
            $seconds,
            $difference,
            "Expected [{$actual}] to be within {$seconds} seconds of [{$expected->toIso8601String()}]."
        );
    }
}
