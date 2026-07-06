<?php

return [

    /*
     |--------------------------------------------------------------------------
     | ModifyDuskBrowserTime middleware registration
     |--------------------------------------------------------------------------
     |
     | Controls where \Paksuco\DuskTimeTravel\Middleware\ModifyDuskBrowserTime
     | is registered. The middleware is inert unless the browser has used the
     | `travelTo()` function, so registering it is usually safe.
     |
     | For available options, see `README.md`
     |
     */

    'middleware' => true,

];
