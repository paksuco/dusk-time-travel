<div style="margin: 50px 0; text-align: center;"><img src=".github/dusk-time-travel.png"><br><br>

## A dusk browser extension package for time traveling

</div>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/paksuco/dusk-time-travel.svg?style=flat-square)](https://packagist.org/packages/paksuco/dusk-time-travel)
[![Total Downloads](https://img.shields.io/packagist/dt/paksuco/dusk-time-travel.svg?style=flat-square)](https://packagist.org/packages/paksuco/dusk-time-travel)


This package feeds the hunger for Dusk test cases having time modified requests. All PR's are welcome.

Supported versions:
- Laravel Dusk 6 → 8
- Laravel Framework 7 → 13
- PHP 7.4 → 8.5

Beyond this the package may continue to work, but is untested. Please raise an issue if you run into problems, so it can be fixed.

## Installation

You can install the package via composer:

```bash
composer require --dev paksuco/dusk-time-travel ^1.0.0
```

There is a crucial step to do after installing the package to let the browser have time travel methods, you need to extend your browser class from `Paksuco/DuskTimeTravel/Browser` class instead of the stock `Laravel/Dusk/Browser`. This class acts like a middle man between your test cases and the Laravel Dusk browser.

To do this, add this code to your `DuskTestCase.php` file:

```php

    use \Paksuco\DuskTimeTravel\Browser as TimeTravelBrowser;

    class DuskTestCase extends BaseTestCase {

        protected function newBrowser($driver)
        {
            return new TimeTravelBrowser($driver);
        }

    }
```

## Usage

Since you've changed your browser class, you've gained access to two new Dusk browser methods:
- `travelTo($time)` - travel through time, using a `Illuminate/Support/Carbon` instance as the time input.
- `travelBack()` - return to the current time.

The traveled time is delivered via a cookie, which has two consequences:
1. The browser must already be on a page of your app when you call `travelTo()` or `travelBack()`, so `visit()` something first or you'll get an `invalid cookie domain` error.
2. The time change (both server-side and browser-side JavaScript) only applies from the **next** page load; the current page is unaffected.

For example:

```php
$this->browse(function ($browser) {
    
    $browser->visit('/')
        // (1) Visit home, (2) Travel to tomorrow
        ->visit("home")
        ->travelTo(Carbon::tomorrow())
        // The home page will show today's date (we have NOT reloaded the page)
        ->assertSee(Carbon::today()->format('Y-m-d'));
});
```
```php
$this->browse(function ($browser) {

    $browser->visit('/')
        // (1) Travel to tomorrow, (2) Visit home
        ->travelTo(Carbon::tomorrow())
        ->visit("home")
        // The home page will show tomorrow's date (we have reloaded the page)
        ->assertSee(Carbon::tomorrow()-format('Y-m-d'));
});
```

Both of them will use tomorrows date as the next request (AJAX or Redirect, doesn't matter).

Other usage examples:

```php
$this->browse(function ($browser) {

    // Do something in yesterdays date and expect to see that it occurred on that date
    $browser->visit('/')
        ->travelTo(Carbon::yesterday())->visit($itemDetailsPage)
        ->doStuffInYesterdaysDate()
        ->travelBack()->visit($itemDetailsPage)
        ->assertSee(Carbon::yesterday());
});
```
```php
$this->browse(function ($browser) {

    // Log in sometime in the distant future (long after session expiry)
    $user = User::factory()->create(['name' => 'Bob']);
    $browser->visit('/')
        ->travelTo(Carbon::parse('2040-01-01 12:00:00')
        ->actingAs($user)->visit('/dashboard')
        ->assertSee("Welcome Bob, it is the year 2040!");
});
```

After you've recreated the instance, or manually reset with `travelBack()`, the server will revert to the normal date.

## Browser-side (JavaScript) time travel

In addition to faking server-side time, `travelTo()` also fakes time for JavaScript running in the browser. `new Date()`, `Date.now()` and plain `Date()` calls in page scripts will return the traveled time. This works by registering a small `Date` shim through the Chrome DevTools Protocol (`Page.addScriptToEvaluateOnNewDocument`), so it:

- takes effect on the **next page load** — consistent with when the server-side time changes,
- survives navigations, and runs **before** any page script on every new document,
- is removed again by `travelBack()`, also taking effect on the next page load.

Note that each page load starts exactly at the traveled instant and then ticks forward naturally (time is shifted, not frozen — freezing would break polling and animation code).

Since the server re-freezes to the traveled time at the start of each request, browser and server agree at page-load time and the browser then drifts forward by the age of the page.

To fake only server-side time, pass `false` as the second argument:

```php
// All PHP versions:
$browser->visit('/')->travelTo(Carbon::tomorrow(), false);

// Or if you'd like to use named parameters (PHP 8.0+):
$browser->visit('/')->travelTo(Carbon::tomorrow(), javascript: false);
```

Limitations:

- Requires Chrome/Chromium (the standard Dusk setup). On other drivers, or if the DevTools command is unavailable, browser-side faking silently degrades and server-side faking continues to work as before.
- Only the zero-argument functions are shifted. Explicit constructions like `new Date(2020, 0, 1)`, `Date.parse()` and `Date.UTC()` behave natively (as you'd expect).
- `performance.now()` and Web Workers are not faked.

## Registering the `ModifyDuskBrowserTime` middleware

By default, the package registers its middleware globally, so every request your application's HTTP kernel handles is covered — `web`, `api`, any custom middleware group, and even routes that don't belong to any group at all.

The middleware is inert unless the browser has actually traveled through time (with `travelTo()`), so this is safe even if your application has no `api` route group, or doesn't use one at all.

If you'd like more control, publish the config file:

```bash
php artisan vendor:publish --tag=dusk-time-travel-config
```

This creates `config/dusk-time-travel.php`:

```php
return [
    'middleware' => true,
];
```

The `middleware` option accepts:

- `true` (default) — register globally, as described above.
- `false` — don't register the middleware anywhere automatically. Use this if you'd rather wire it up yourself.
- an array of middleware group names, e.g. `['web']` — register only on those groups instead of globally. Group names that you define but don't actually exist will be silently skipped.

**Note on Laravel 7.x:** Defining an array of middleware group names is only supported on Laravel 8 onwards. If an array is defined in the config file on Laravel 7, it will instead be silently handled as if you'd set it to boolean true instead and the middleware will be registered globally.

## Testing

A test case is included in this respository, but since it's a Dusk extension the tests are run on a Laravel instance having Dusk installed. You can test the plugin the same way the `.github/workflows/run-tests.yml` workflow does.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Taha Paksu](https://github.com/tpaksu) (initial developer)
- [Shane Smith](https://github.com/shane-smith) (current maintainer)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
