<?php

namespace Daanra\LaravelLetsEncrypt;

use Daanra\LaravelLetsEncrypt\Commands\LetsEncryptGenerateCommand;
use Illuminate\Support\ServiceProvider;
use Spatie\Skeleton\Commands\SkeletonCommand;

class LetsEncryptServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/lets_encrypt.php' => config_path('lets_encrypt.php'),
            ], 'config');

            $this->commands([
                LetsEncryptGenerateCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/skeleton.php', 'skeleton');
    }
}
