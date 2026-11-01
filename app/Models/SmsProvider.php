<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class SmsProvider extends Model
{
    protected $fillable = [
        'name',
        'driver',
        'base_url',
        'api_version',
        'api_token',
        'sender_id',
        'sender_type',
        'business_name',
        'price_per_sms',
        'balance',
        'estimated_sms',
        'sandbox_mode',
        'is_active',
        'is_default',
        'last_connected_at',
        'last_balance_synced_at',
        'extra_metadata',
    ];

    protected $casts = [
        'sandbox_mode'           => 'boolean',
        'is_active'              => 'boolean',
        'is_default'             => 'boolean',
        'price_per_sms'          => 'decimal:4',
        'balance'                => 'decimal:2',
        'estimated_sms'          => 'integer',
        'extra_metadata'         => 'array',
        'last_connected_at'      => 'datetime',
        'last_balance_synced_at' => 'datetime',
    ];

    /**
     * Automatically encrypt API token when saving to database
     */
    public function setApiTokenAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['api_token'] = null;
            return;
        }

        try {
            // Jodi already encrypted string hoy, tahole jemon ache temon-i thakbe
            Crypt::decryptString($value);
            $this->attributes['api_token'] = $value;
        } catch (Throwable $e) {
            // Plain text hole encrypt kore database-e save korbe
            $this->attributes['api_token'] = Crypt::encryptString(trim((string) $value));
        }
    }

    /**
     * Automatically decrypt API token when reading (Blade input ebong Controller-er jonno)
     */
    public function getApiTokenAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable $e) {
            return $value;
        }
    }

    /**
     * Safe decrypted token accessor for backend services and drivers
     */
    public function getDecryptedTokenAttribute(): ?string
    {
        return $this->api_token;
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(SmsCampaign::class, 'sms_provider_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SmsLog::class, 'provider', 'name');
    }
}