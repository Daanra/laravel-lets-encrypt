<?php

namespace Daanra\LaravelLetsEncrypt;

use AcmePhp\Core\Http\Base64SafeEncoder;
use AcmePhp\Core\Http\SecureHttpClientFactory;
use AcmePhp\Core\Http\ServerErrorHandler;
use AcmePhp\Ssl\Parser\KeyParser;
use AcmePhp\Ssl\Signer\DataSigner;
use Daanra\LaravelLetsEncrypt\Commands\LetsEncryptGenerateCommand;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\ServiceProvider;

class LetsEncryptServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/lets_encrypt.php' => config_path('lets_encrypt.php'),
            ]);

            $migrationFileName = 'create_lets_encrypt_certificates_table.php';
            if (! $this->migrationFileExists($migrationFileName)) {
                $this->publishes([
                    __DIR__ . "/../database/migrations/{$migrationFileName}.stub" => database_path('migrations/' . date('Y_m_d_His', time()) . '_' . $migrationFileName),
                ]);
            }
        }

        $this->commands([
            LetsEncryptGenerateCommand::class,
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/lets_encrypt.php', 'lets_encrypt');
        $this->app->bind('lets-encrypt', function () {
            return new LetsEncrypt(
                new SecureHttpClientFactory(
                    new GuzzleHttpClient(),
                    new Base64SafeEncoder(),
                    new KeyParser(),
                    new DataSigner(),
                    new ServerErrorHandler()
                )
            );
        });
    }

    public static function migrationFileExists(string $migrationFileName): bool
    {
        $len = strlen($migrationFileName);
        foreach (glob(database_path("migrations/*.php")) as $filename) {
            if ((substr($filename, -$len) === $migrationFileName)) {
                return true;
            }
        }

        return false;
    }
}
