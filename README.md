# A dusk browser extension package for time traveling

[![Latest Version on Packagist](https://img.shields.io/packagist/v/paksuco/dusk-time-travel.svg?style=flat-square)](https://packagist.org/packages/paksuco/dusk-time-travel)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/paksuco/dusk-time-travel/run-tests?label=tests)](https://github.com/paksuco/dusk-time-travel/actions?query=workflow%3ATests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/paksuco/dusk-time-travel.svg?style=flat-square)](https://packagist.org/packages/paksuco/dusk-time-travel)


This package feeds the hunger for Dusk test cases having time modified requests. All PR's are welcome since it's still not mature enough, and might not work as expected. But I think it's easy to understand what this package does.

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

Since you've changed your browser class, you've gained access to two new Dusk browser methods: **travelTo($time)** and **travelBack**. As you can easily understand from the names, first travels through time, uses a `Illuminate/Support/Carbon` instance as the time input, and second brings it back.

**Note**: As it's using cookies to deliver the modified time to the browser, only the next requests will be affected with the changed time, the current page won't be having the date modified.

For example:

```php
// test case
$this->browse(function ($browser) {

    // on the homepage, you will see today's date as the current date.
    $browser->visit("home")
        ->travelTo(Carbon::tomorrow());

    // but, like this, you'll see tomorrows date as the current date
    $browser->travelTo(Carbon::tomorrow())
        ->visit("home");


    // an example use case, do something in yesterdays date and expect it to see today.
    $browser->travelTo(Carbon::yesterday())->visit($itemDetailsPage)
        ->doStuffInYesterdaysDate()
        ->travelBack()->visit($itemDetailsPage)
        ->assertSee(Carbon::yesterday());
});
```

Both of them will use tomorrows date as the next request (AJAX or Redirect, doesn't matter).

After you've recreated the instance, or manually reset the date back to current by using **travelBack**, the date server uses will revert to normal.
<br><br>


## Testing
A test case is included, but since it's a Dusk extension, the tests are run on a Laravel instance having Dusk installed. You can test the plugin the same way `.github/workflows/run-tests.yml` workflow does.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Taha Paksu](https://github.com/tpaksu)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
