# A dusk browser extension package for time traveling

[![Latest Version on Packagist](https://img.shields.io/packagist/v/paksuco/dusk-time-travel.svg?style=flat-square)](https://packagist.org/packages/paksuco/dusk-time-travel)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/paksuco/dusk-time-travel/run-tests?label=tests)](https://github.com/paksuco/dusk-time-travel/actions?query=workflow%3ATests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/paksuco/dusk-time-travel.svg?style=flat-square)](https://packagist.org/packages/paksuco/dusk-time-travel)


This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/package-dusk-time-travel-laravel.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/package-dusk-time-travel-laravel)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require paksuco/dusk-time-travel
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Paksuco\DuskTimeTravel\DuskTimeTravelServiceProvider" --tag="migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Paksuco\DuskTimeTravel\DuskTimeTravelServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$dusk-time-travel = new Paksuco\DuskTimeTravel();
echo $dusk-time-travel->echoPhrase('Hello, Paksuco!');
```

## Testing

```bash
composer test
```

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
