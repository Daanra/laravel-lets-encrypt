<?php

namespace Daanra\LaravelLetsEncrypt\Support;

use Daanra\LaravelLetsEncrypt\Contracts\PathGenerator;
use Daanra\LaravelLetsEncrypt\Exceptions\InvalidPathGenerator;

class PathGeneratorFactory
{
    public static function create(): PathGenerator
    {
        $class = config('lets_encrypt.path_generator');

        throw_if($class === null, new InvalidPathGenerator('null'));

        throw_if(! class_exists($class), new InvalidPathGenerator($class));

        return app($class);
    }
}
