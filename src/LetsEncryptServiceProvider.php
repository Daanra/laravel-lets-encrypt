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
                __DIR__.'/../config/skeleton.php' => config_path('skeleton.php'),
            ], 'config');

            $this->commands([
                LetsEncryptGenerateCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'skeleton');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/skeleton.php', 'skeleton');
    }
}
