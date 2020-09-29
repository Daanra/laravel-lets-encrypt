<?php

namespace Daanra\LaravelLetsEncrypt\Commands;

use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

class LetsEncryptGenerateCommand extends Command
{
    protected $name = 'lets-encrypt:create';

    protected $description = 'Creates an SSL certificate through Let\'s Encrypt.';

    public function handle()
    {
        if (! $domains = $this->option('domain')) {
            $domains = $this->ask('For which domain do you want to create an SSL certificate? [Separate multiple domains with comma\'s]');
        }
        $domains = collect(explode(',', $domains));

        $this->comment('Generating certificates for ' . count($domains) . ' domains.');
        $domains->each(function (string $domain) {
            $this->comment($domain  . ':');

            rescue(function () use ($domain) {
                LetsEncrypt::create($domain);
            }, function (Throwable $e) use ($domain) {
                $this->error('Failed to generate a certificate for ' . $domain);
                $this->error($e->getMessage());
                $this->comment('');
            }, false);
        });

    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['domain', 'd', InputOption::VALUE_OPTIONAL, 'Generate a certificate for the given domain name(s). Multiple domains can be separated by a comma.'],
        ];
    }
}
