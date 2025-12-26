<div style="margin: 50px 0; text-align: center;"><img src=".github/dusk-time-travel.png"><br><br>

## A dusk browser extension package for time traveling

</div>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/paksuco/dusk-time-travel.svg?style=flat-square)](https://packagist.org/packages/paksuco/dusk-time-travel)
[![Total Downloads](https://img.shields.io/packagist/dt/paksuco/dusk-time-travel.svg?style=flat-square)](https://packagist.org/packages/paksuco/dusk-time-travel)


This package feeds the hunger for Dusk test cases having time modified requests. All PR's are welcome.

Supported versions:
- Laravel Dusk 6 → 8
- Laravel Framework 7 → 12
- PHP 7.4 → 8.5

Beyond this the package may continue to work, but is untested. Please raise an issue if you run into problems, so it can be fixed.

## Installation

You can install the package via composer:

```bash
composer require paksuco/dusk-time-travel
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

**Note**: As the package uses cookies to deliver the modified time to the browser, only the next requests will use the changed time, the current page won't have the date modified. For logging in, this means that may need to change the time and _then_ load the login page.

For example:

```php
$this->browse(function ($browser) {

    // The home page will show today's date
    $browser->visit("home")
        ->travelTo(Carbon::tomorrow());
});
```
```php
$this->browse(function ($browser) {

    // The home page will show tomorrow's date
    $browser->travelTo(Carbon::tomorrow())
        ->visit("home");
});
```

Both of them will use tomorrows date as the next request (AJAX or Redirect, doesn't matter).

Other usage examples:

```php
$this->browse(function ($browser) {

    // Do something in yesterdays date and expect to see that it occurred on that date
    $browser->travelTo(Carbon::yesterday())->visit($itemDetailsPage)
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

## Testing
A test case is included, but since it's a Dusk extension, the tests are run on a Laravel instance having Dusk installed. You can test the plugin the same way the `.github/workflows/run-tests.yml` workflow does.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Taha Paksu](https://github.com/tpaksu)
- [Shane Smith](https://github.com/shane-smith)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
