# Let's Encrypt for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/daanra/laravel-lets-encrypt.svg?style=flat-square)](https://packagist.org/packages/daanra/laravel-lets-encrypt)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/daanra/laravel-lets-encrypt/run-tests?label=tests)](https://github.com/daanra/laravel-lets-encrypt/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/daanra/laravel-lets-encrypt.svg?style=flat-square)](https://packagist.org/packages/daanra/laravel-lets-encrypt)

A Laravel package for generating SSL certificates using Let's Encrypt.

## Installation

You can install the package via composer:

```bash
composer require daanra/laravel-lets-encrypt
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Daanra\LaravelLetsEncrypt\LetsEncryptServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

``` php
$skeleton = new Spatie\Skeleton();
echo $skeleton->echoPhrase('Hello, Spatie!');
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email daanraatjes+dev@gmail.com instead of using the issue tracker.

## Credits

- [Daan Raatjes](https://github.com/Daanra)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
