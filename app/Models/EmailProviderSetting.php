<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailProviderSetting extends Model
{
    use HasFactory;

    protected $table = 'email_provider_settings';

    protected $fillable = [
        'name',
        'driver',
        'is_active',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'api_key',
        'region',
        'sender_name',
        'sender_email',
        'reply_to',
        'hourly_limit',
        'daily_limit',
        'batch_size',
    ];

    /**
     * Hidden from serialization to prevent accidental leakage in JSON/Array outputs
     */
    protected $hidden = [
        'password',
        'api_key',
    ];

    /**
     * Automatic encryption & type casting
     */
    protected $casts = [
        'is_active'    => 'boolean',
        'port'         => 'integer',
        'hourly_limit' => 'integer',
        'daily_limit'  => 'integer',
        'batch_size'   => 'integer',
        'password'     => 'encrypted',
        'api_key'      => 'encrypted',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes (High-Performance Query Filters)
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Currently active email provider
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by driver type (smtp, ses, brevo, etc.)
     */
    public function scopeByDriver($query, string $driver)
    {
        return $query->where('driver', $driver);
    }

    /*
    |--------------------------------------------------------------------------
    | Static Getters & State Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get the currently active provider instance
     */
    public static function getActive(): ?self
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Make this provider the single active one and deactivate others
     */
    public function makeActive(): bool
    {
        self::where('id', '!=', $this->id)->update(['is_active' => false]);
        return $this->update(['is_active' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | Runtime Mail Configuration Mappers
    |--------------------------------------------------------------------------
    */

    /**
     * Generate dynamic Laravel mailer configuration array for runtime binding
     */
    public function toMailerConfig(): array
    {
        return match ($this->driver) {
            'ses' => [
                'transport' => 'ses',
                'key'       => $this->api_key,
                'secret'    => $this->password,
                'region'    => $this->region ?? 'us-east-1',
            ],
            'mailgun' => [
                'transport' => 'mailgun',
                'domain'    => $this->host,
                'secret'    => $this->api_key,
                'endpoint'  => 'api.mailgun.net',
                'scheme'    => 'https',
            ],
            'postmark' => [
                'transport' => 'postmark',
                'token'     => $this->api_key,
            ],
            default => [
                'transport'  => 'smtp',
                'host'       => $this->host,
                'port'       => $this->port,
                'encryption' => $this->encryption !== 'none' ? $this->encryption : null,
                'username'   => $this->username,
                'password'   => $this->password,
                'timeout'    => 30,
            ],
        };
    }

    /**
     * Get masked representation of password for secure Admin UI display
     */
    public function getMaskedPasswordAttribute(): string
    {
        return !empty($this->password) ? '••••••••••••' : '—';
    }

    /**
     * Get masked representation of API key for secure Admin UI display
     */
    public function getMaskedApiKeyAttribute(): string
    {
        if (empty($this->api_key)) {
            return '—';
        }
        $len = strlen($this->api_key);
        return $len > 6 
            ? str_repeat('•', 8) . substr($this->api_key, -4) 
            : '••••••';
    }
}