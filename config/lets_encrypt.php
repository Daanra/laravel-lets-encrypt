<?php

use Daanra\LaravelLetsEncrypt\Support\DefaultPathGenerator;

return [

    // Important, change to production LE directory for real certs!
    'api_url' => env('LETS_ENCRYPT_API_URL', 'https://acme-staging-v02.api.letsencrypt.org/directory'),

    // The path to the public ssl key used for connecting with the let's encrypt API.
    // A fresh key will be generated if it does not exist yet.
    'public_key_path' => env('LETS_ENCRYPT_PUBLIC_KEY_PATH', storage_path('app/lets-encrypt/keys/account.pub.pem')),

    // The path to the private ssl key used for connecting with the let's encrypt API.
    // A fresh key will be generated if it does not exist yet.
    'private_key_path' => env('LETS_ENCRYPT_PRIVATE_KEY_PATH', storage_path('app/lets-encrypt/keys/account.pem')),

    // Universal email address, every certificate will be issued using this email address by default.
    // Only useful if you want to receive emails about expiring certificates
    'universal_email_address' => env('LETS_ENCRYPT_UNIVERSAL_EMAIL_ADDRESS', null),

    'path_generator' => DefaultPathGenerator::class,

    // The disk to store the certificates on. Use null for the default disk.
    'certificate_disk' => null,

    // The disk to store store challenges on. Use null for the default disk.
    'challenge_disk' => null,
];
