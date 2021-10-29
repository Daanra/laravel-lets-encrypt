<?php

namespace Daanra\LaravelLetsEncrypt\Models;

use Daanra\LaravelLetsEncrypt\Builders\LetsEncryptCertificateBuilder;
use Daanra\LaravelLetsEncrypt\Collections\LetsEncryptCertificateCollection;
use Daanra\LaravelLetsEncrypt\Facades\LetsEncrypt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $domain
 * @property string|null $fullchain_path
 * @property string|null $chain_path
 * @property string|null $privkey_path
 * @property string|null $cert_path
 * @property bool $created
 * @property \Illuminate\Support\Carbon|null $last_renewed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read bool $has_expired
 * @method static \Daanra\LaravelLetsEncrypt\Builders\LetsEncryptCertificateBuilder|\Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate query()
 * @method static \Daanra\LaravelLetsEncrypt\Builders\LetsEncryptCertificateBuilder|\Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate newQuery()
 * @method static \Daanra\LaravelLetsEncrypt\Builders\LetsEncryptCertificateBuilder|\Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate newModelQuery()
 * @method static \Daanra\LaravelLetsEncrypt\Builders\LetsEncryptCertificateBuilder|\Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate withTrashed()
 * @method static \Daanra\LaravelLetsEncrypt\Builders\LetsEncryptCertificateBuilder|\Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate withoutTrashed()
 * @method static \Daanra\LaravelLetsEncrypt\Builders\LetsEncryptCertificateBuilder|\Daanra\LaravelLetsEncrypt\Models\LetsEncryptCertificate whereDomain($value)
 * @method static bool|null forceDelete()
 * @method static bool|null restore()
 * @method static static create(array $attributes)
 * @mixin \Eloquent
 */
class LetsEncryptCertificate extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $dates = ['last_renewed_at'];

    protected $casts = [
        'created' => 'boolean',
    ];

    public function newEloquentBuilder($query): LetsEncryptCertificateBuilder
    {
        return new LetsEncryptCertificateBuilder($query);
    }

    public function newCollection(array $models = [])
    {
        return new LetsEncryptCertificateCollection($models);
    }

    public function getHasExpiredAttribute(): bool
    {
        return $this->last_renewed_at && $this->last_renewed_at->diffInDays(now()) >= 90;
    }

    public function renew()
    {
        return LetsEncrypt::renew($this);
    }

    public function renewNow(): self
    {
        return LetsEncrypt::renewNow($this);
    }
}
