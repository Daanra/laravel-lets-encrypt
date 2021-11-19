# Let's Encrypt Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/daanra/laravel-lets-encrypt.svg?style=flat-square)](https://packagist.org/packages/daanra/laravel-lets-encrypt)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/daanra/laravel-lets-encrypt/run-tests?label=tests)](https://github.com/daanra/laravel-lets-encrypt/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/daanra/laravel-lets-encrypt.svg?style=flat-square)](https://packagist.org/packages/daanra/laravel-lets-encrypt)

A Laravel package for easily generating and renewing SSL certificates using Let's Encrypt. This package is especially useful if
you have a Laravel application that manages the SSL certificates of many domains. This package is **not** recommended if
you just need to generate a single SSL certificate for your application.

This package is essentially a Laravel-friendly wrapper around [Acme PHP](https://github.com/acmephp/acmephp). 

## Installation

You can install the package via composer:

```bash
composer require daanra/laravel-lets-encrypt
```

Publish the configuration file and the migration:

```bash
php artisan vendor:publish --provider="Daanra\LaravelLetsEncrypt\LetsEncryptServiceProvider" --tag="lets-encrypt"
```

Run the migration:
```bash
php artisan migrate
```

**Note:**

You somehow have to return a stored challenge whenever it it retrieved from the `/.well-known/acme-challenge` endpoint. You could do this by configuring NGINX/Apache appropriately or by registering a route:
```php
Route::get('/.well-known/acme-challenge/{token}', function (string $token) {
    return \Illuminate\Support\Facades\Storage::get('public/.well-known/acme-challenge/' . $token);
})
```

Sometimes the `/.well-known/` prefix is disabled by default in the NGINX/Apache config (see [#4](https://github.com/Daanra/laravel-lets-encrypt/issues/4)). Make sure it is forwarded to your Laravel application if you want Laravel to return the challenge.


## Usage

Creating a new SSL certificate for a specific domain is easy:
```php
// Puts several jobs on the queue to handle the communication with the lets-encrypt server
[$certificate, $pendingDispatch] = \Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt::create('mydomain.com');

// You could, for example, chain some jobs to enable a new virtual host
// in Apache and send a notification once the website is available
[$certificate, $pendingDispatch] = \Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt::create('mydomain.com', [
    new CreateNewApacheVirtualHost('mydomain.com'), 
    new ReloadApache(),
    new NotifyUserOfNewCertificate(request()->user()),
]);

// You can also do it synchronously:
\Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt::createNow('mydomain.com');
```

Alternative syntax available from v0.3.0:

```php
LetsEncrypt::certificate('mydomain.com')
            ->chain([
                new SomeJob()
            ])
            ->delay(5)
            ->retryAfter(4)
            ->setTries(4)
            ->setRetryList([1, 5, 10])
            ->create(); // or ->renew()
```

Where you can specify values for all jobs:

- tries (The number of times the job may be attempted)
- retryAfter (The number of seconds to wait before retrying the job)
- retryList (The list of seconds to wait before retrying the job)
- chain (Chain some jobs after the certificate has successfully been obtained)
- delay (Set the desired delay for the job)

You could also achieve the same by using an artisan command:
```bash
php artisan lets-encrypt:create -d mydomain.com
```

Certificates are stored in the database. You can query them like so:
```php
// All certificates
\Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate::all();
// All expired certificates
\Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate::query()->expired()->get();
// All currently valid certificates
\Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate::query()->valid()->get();
// All certificates that should be renewed (because they're more than 60 days old)
\Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate::query()->requiresRenewal()->get();

// Find certificate by domain
$certificate = LetsEncryptCertificate::where('domain', 'mydomain.com')->first();
// If you no longer need it, you can soft delete
$certificate->delete();
// Or use a hard delete
$certificate->forceDelete();
```

## Failure events

If one of the jobs fails, one of the following events will be dispatched:
```php
Daanra\LaravelLetsEncrypt\Events\CleanUpChallengeFailed
Daanra\LaravelLetsEncrypt\Events\ChallengeAuthorizationFailed
Daanra\LaravelLetsEncrypt\Events\RegisterAccountFailed
Daanra\LaravelLetsEncrypt\Events\RequestAuthorizationFailed
Daanra\LaravelLetsEncrypt\Events\RequestCertificateFailed
Daanra\LaravelLetsEncrypt\Events\StoreCertificateFailed
Daanra\LaravelLetsEncrypt\Events\RenewExpiringCertificatesFailed
```

Every event implements the `Daanra\LaravelLetsEncrypt\Interfaces\LetsEncryptCertificateFailed` interface so you can listen for that as well.

## Automatically renewing certificates

Certificates are valid for 90 days. Before those 90 days are over, you will want to renew them. To do so, you
could add the following to your `App\Console\Kernel`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->job(new \Daanra\LaravelLetsEncrypt\Jobs\RenewExpiringCertificates)->daily();
}
```

This will automatically renew every certificate that is older than 60 days, ensuring that they never expire.

## Configuration

By default this package will use Let's Encrypt's staging server to issue certificates. You should set: 
```bash
LETS_ENCRYPT_API_URL=https://acme-v02.api.letsencrypt.org/directory
```
in the `.env` file of your production server.


By default, this package will attempt to validate a certificate using [a HTTP-01 challenge](https://letsencrypt.org/docs/challenge-types/).
For this reason, a file will be temporarily stored in your application's storage directory under the path 
`app/public/.well-known/acme-challenge/<CHALLENGE_TOKEN>`. You can customise this behavior by setting a custom
`PathGenerator` class in your config under `path_generator`. Note that Let's Encrypt expects the following path:
```bash
/.well-known/acme-challenge/<CHALLENGE_TOKEN>
```
to return the contents of the file located at `$pathGenerator->getPath($token)`.


## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email opensource@daanraatjes.dev instead of using the issue tracker. If you have a question, please open an issue instead of sending an email.

## Credits

- [Daan Raatjes](https://github.com/Daanra)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
