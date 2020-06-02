<?php

namespace Daanra\LaravelLetsEncrypt\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class LetsEncryptGenerateCommand extends Command
{
    public $signature = 'lets-encrypt:generate 
            {domain* : The domains for which you want to generate a certificate}
    ';

    public $description = 'Generates an SSL certificate through Let\'s Encrypt.';

    public function handle()
    {
        if (! $domains = $this->argument('domain')) {
            $domains = $this->ask('For which domain do you want to generate an SSL certificate? [Separate multiple domains with comma\'s]');
        }


        $this->comment(implode($domains, ' <> '));
    }
}
