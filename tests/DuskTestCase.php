<?php

namespace Paksuco\DuskTimeTravel\Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;
use Paksuco\DuskTimeTravel\Browser as TimeTravelEnabledBrowser;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        if (! static::runningInSail()) {
            static::startChromeDriver();
        }
    }

    protected function newBrowser($driver)
    {
        return new TimeTravelEnabledBrowser($driver);
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $browserCapabilities = DesiredCapabilities::chrome();
        $optionsBase = new ChromeOptions;

        $options = $optionsBase->addArguments([
            '--disable-gpu',
            '--headless',
            '--no-sandbox',
            '--window-size=1920,1080',
        ]);

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            $browserCapabilities
                ->setCapability(ChromeOptions::CAPABILITY, $options)
                ->setCapability('acceptInsecureCerts', true)
        );
    }
}
