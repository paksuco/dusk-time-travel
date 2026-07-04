<?php

namespace Paksuco\DuskTimeTravel;

use Exception;
use Illuminate\Support\Carbon;
use Laravel\Dusk\Browser as DuskBrowser;

class Browser extends DuskBrowser
{
    /**
     * CDP identifier of the Date shim registered via
     * Page.addScriptToEvaluateOnNewDocument, if any.
     *
     * Dusk reuses Browser instances across tests in a class, so the
     * identifier lives on the instance and is replaced on re-travel.
     *
     * @var string|null
     */
    protected $timeTravelScriptIdentifier = null;

    /**
     * Travels to the specified time
     *
     * @param   Carbon  $time       The time to mimic on the browser
     * @param   bool    $javascript Whether to also fake JavaScript Date in the browser
     *
     * @return  Browser             The browser instance to support chaining
     */
    public function travelTo(Carbon $time, $javascript = true)
    {
        return tap($this, function (DuskBrowser $browser) use ($time, $javascript) {
            $browser->plainCookie("dusk-skip-time", $time->toIso8601String());

            if ($javascript) {
                $this->fakeBrowserTime($time);
            }
        });
    }

    /**
     * Returns back to the current time
     *
     * @return  Browser        The browser instance to support chaining
     */
    public function travelBack()
    {
        return tap($this, function (DuskBrowser $browser) {
            $browser->deleteCookie("dusk-skip-time");
            $this->restoreBrowserTime();
        });
    }

    /**
     * Overrides the browser's JavaScript Date so pages see the traveled time.
     *
     * The shim is registered to run before any page script on every new
     * document. The currently loaded page is deliberately left untouched so
     * browser time changes on the next request, consistent with when the
     * server-side time changes.
     *
     * @param   Carbon  $time  The time the browser should believe it is
     *
     * @return  void
     */
    protected function fakeBrowserTime(Carbon $time)
    {
        $this->removeNewDocumentScript();

        $result = $this->sendDevToolsCommand('Page.addScriptToEvaluateOnNewDocument', [
            'source' => BrowserTimeFaker::shimSource($time),
        ]);

        if (is_array($result) && isset($result['identifier'])) {
            $this->timeTravelScriptIdentifier = $result['identifier'];
        }
    }

    /**
     * Stops faking the browser's Date from the next page load onwards.
     *
     * @return  void
     */
    protected function restoreBrowserTime()
    {
        $this->removeNewDocumentScript();
    }

    /**
     * Unregisters the previously injected new-document script, if any.
     *
     * @return  void
     */
    protected function removeNewDocumentScript()
    {
        if ($this->timeTravelScriptIdentifier === null) {
            return;
        }

        $this->sendDevToolsCommand('Page.removeScriptToEvaluateOnNewDocument', [
            'identifier' => $this->timeTravelScriptIdentifier,
        ]);

        $this->timeTravelScriptIdentifier = null;
    }

    /**
     * Sends a Chrome DevTools Protocol command through the WebDriver session.
     *
     * @param   string  $command  The CDP command name
     * @param   array   $params   The CDP command parameters
     *
     * @return  mixed|null        The CDP result, or null when CDP is
     *                            unavailable (non-Chrome driver or old
     *                            chromedriver) — browser-side time faking
     *                            silently degrades to server-side only
     */
    protected function sendDevToolsCommand($command, array $params)
    {
        if (! method_exists($this->driver, 'executeCustomCommand')) {
            return null;
        }

        try {
            return $this->driver->executeCustomCommand(
                '/session/:sessionId/goog/cdp/execute',
                'POST',
                ['cmd' => $command, 'params' => (object) $params]
            );
        } catch (Exception $e) {
            return null;
        }
    }
}
