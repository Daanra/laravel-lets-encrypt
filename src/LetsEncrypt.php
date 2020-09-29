<?php

namespace Daanra\LaravelLetsEncrypt;

use AcmePhp\Core\AcmeClient;
use AcmePhp\Core\Http\SecureHttpClientFactory;
use AcmePhp\Ssl\Generator\KeyPairGenerator;
use AcmePhp\Ssl\KeyPair;
use AcmePhp\Ssl\PrivateKey;
use AcmePhp\Ssl\PublicKey;
use Daanra\LaravelLetsEncrypt\Exceptions\DomainAlreadyExists;
use Daanra\LaravelLetsEncrypt\Exceptions\InvalidDomainException;
use Daanra\LaravelLetsEncrypt\Exceptions\InvalidKeyPairConfiguration;
use Daanra\LaravelLetsEncrypt\Jobs\RegisterAccount;
use Daanra\LaravelLetsEncrypt\Jobs\RequestAuthorization;
use Daanra\LaravelLetsEncrypt\Jobs\RequestCertificate;
use Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate;
use Illuminate\Foundation\Bus\PendingChain;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LetsEncrypt
{
    /** @var \AcmePhp\Core\Http\SecureHttpClientFactory */
    protected $factory;

    /**
     * LetsEncrypt constructor.
     * @param SecureHttpClientFactory $factory
     */
    public function __construct(SecureHttpClientFactory $factory)
    {
        $this->factory = $factory;
    }

    public function create(string $domain): PendingDispatch
    {
        if (Str::contains($domain, [':', '/', ','])) {
            throw new InvalidDomainException($domain);
        }

        if (LetsEncryptCertificate::withTrashed()->where('domain', $domain)->exists()) {
            throw new DomainAlreadyExists($domain);
        }

        $email = config('lets_encrypt.universal_email_address', false);

        return RegisterAccount::withChain([
            new RequestAuthorization($domain),
            new RequestCertificate($domain),
        ])->dispatch();
    }

    public function renew(string $domain): PendingDispatch
    {
        if (Str::contains($domain, [':', '/', ','])) {
            throw new InvalidDomainException($domain);
        }

        $email = config('lets_encrypt.universal_email_address', false);

        return Bus::chain([
            new RegisterAccount($email),
            new RequestAuthorization($domain),
            new RequestCertificate($domain),
        ])->dispatch();
    }

    /**
     * @return AcmeClient
     * @throws InvalidKeyPairConfiguration
     */
    public function createClient(): AcmeClient
    {
        $keyPair = $this->getKeyPair();
        $secureHttpClient = $this->factory->createSecureHttpClient($keyPair);

        return new AcmeClient(
            $secureHttpClient,
            config('lets_encrypt.api_url', 'https://acme-staging-v02.api.letsencrypt.org/directory')
        );
    }

    /**
     * Retrieves a key pair or creates a new one if it does not exist.
     * @return KeyPair
     * @throws InvalidKeyPairConfiguration
     */
    protected function getKeyPair(): KeyPair
    {
        $publicKeyPath = config('lets_encrypt.public_key_path', storage_path('app/lets-encrypt/keys/account.pub.pem'));
        $privateKeyPath = config('lets_encrypt.private_key_path', storage_path('app/lets-encrypt/keys/account.pem'));

        if (! file_exists($privateKeyPath) && ! file_exists($publicKeyPath)) {
            $keyPairGenerator = new KeyPairGenerator();
            $keyPair = $keyPairGenerator->generateKeyPair();

            File::ensureDirectoryExists(File::dirname($publicKeyPath));
            File::ensureDirectoryExists(File::dirname($privateKeyPath));

            file_put_contents($publicKeyPath, $keyPair->getPublicKey()->getPEM());
            file_put_contents($privateKeyPath, $keyPair->getPrivateKey()->getPEM());

            return $keyPair;
        }

        if (! file_exists($privateKeyPath)) {
            throw new InvalidKeyPairConfiguration('Private key does not exist but public key does.');
        }

        if (! file_exists($publicKeyPath)) {
            throw new InvalidKeyPairConfiguration('Public key does not exist but private key does.');
        }

        $publicKey = new PublicKey(file_get_contents($publicKeyPath));
        $privateKey = new PrivateKey(file_get_contents($privateKeyPath));

        return new KeyPair($publicKey, $privateKey);
    }
}
