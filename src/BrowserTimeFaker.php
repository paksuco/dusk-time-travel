<?php

namespace Paksuco\DuskTimeTravel;

use Illuminate\Support\Carbon;

class BrowserTimeFaker
{
    /**
     * Builds the JavaScript Date shim targeting the given time.
     *
     * @param   Carbon  $time  The time the browser should believe it is
     *
     * @return  string         The JavaScript source to inject
     */
    public static function shimSource(Carbon $time)
    {
        $source = file_get_contents(__DIR__ . '/../resources/js/date-shim.js');

        return str_replace('__DUSK_TARGET_MS__', (string) static::targetMilliseconds($time), $source);
    }

    /**
     * Converts a Carbon instance to an epoch timestamp in milliseconds.
     *
     * @param   Carbon  $time  The time to convert
     *
     * @return  int            Milliseconds since the Unix epoch
     */
    public static function targetMilliseconds(Carbon $time)
    {
        return $time->getTimestamp() * 1000 + (int) $time->format('v');
    }
}
