<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EmailSubscriber extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'email_subscribers';

    /**
     * Runtime cache for valid database columns and one-time auto-sync flag
     */
    protected static ?array $validColumnsCache = null;
    protected static bool $hasRunAutoBackfill = false;

    protected $fillable = [
        'user_id',
        'email',
        'name',
        'first_name',
        'last_name',
        'phone',
        'status',
        'source',
        'tags',
        'ip_address',
        'user_agent',
        'marketing_consent',
        'consent_at',
        'consent_source',
        'consent_ip',
        'unsubscribe_token',
        'subscribed_at',
        'unsubscribed_at',
        'unsubscribe_reason',
    ];

    protected $casts = [
        'tags'              => 'array',
        'marketing_consent' => 'boolean',
        'consent_at'        => 'datetime',
        'subscribed_at'     => 'datetime',
        'unsubscribed_at'   => 'datetime',
    ];

    /**
     * Get actual existing columns in email_subscribers table to prevent SQL 1054 errors
     */
    public static function getTableColumns(): array
    {
        if (self::$validColumnsCache === null) {
            self::$validColumnsCache = Schema::hasTable('email_subscribers')
                ? Schema::getColumnListing('email_subscribers')
                : [];
        }

        return self::$validColumnsCache;
    }

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically sync any missed Orders & Form Submissions when loading Email Marketing pages
        if (!self::$hasRunAutoBackfill && !app()->runningInConsole() && request()->is('admin/email-marketing*')) {
            self::$hasRunAutoBackfill = true;
            static::syncMissedOrdersAndSubmissions();
        }

        // Automatically assign a secure 64-character unsubscribe token on creation
        static::creating(function ($subscriber) {
            $cols = self::getTableColumns();

            if (in_array('unsubscribe_token', $cols, true) && empty($subscriber->unsubscribe_token)) {
                $subscriber->unsubscribe_token = Str::random(64);
            }
            if (in_array('consent_at', $cols, true) && empty($subscriber->consent_at) && $subscriber->marketing_consent) {
                $subscriber->consent_at = now();
            }
            if (in_array('subscribed_at', $cols, true) && empty($subscriber->subscribed_at) && $subscriber->status === 'subscribed') {
                $subscriber->subscribed_at = now();
            }

            // Clean email and remove any soft-deleted duplicate before insert
            if (!empty($subscriber->email)) {
                $cleanEmail = strtolower(trim($subscriber->email));
                $subscriber->email = $cleanEmail;

                $trashed = DB::table('email_subscribers')
                    ->where('email', $cleanEmail)
                    ->whereNotNull('deleted_at')
                    ->first();

                if ($trashed) {
                    DB::table('email_subscribers')->where('id', $trashed->id)->delete();
                }
            }
        });

        // Strip any non-existent column attributes before executing INSERT or UPDATE query
        static::saving(function ($subscriber) {
            $cols = self::getTableColumns();
            if (!empty($cols)) {
                foreach (array_keys($subscriber->getAttributes()) as $attrKey) {
                    if (!in_array($attrKey, $cols, true)) {
                        unset($subscriber->{$attrKey});
                    }
                }
            }
        });
    }

    /**
     * Safe Subscriber Sync Helper (Used by Checkout, Dynamic Forms, and Auto-Backfill)
     */
    public static function syncSubscriberSafe(array $data): ?self
    {
        try {
            $email = strtolower(trim((string) ($data['email'] ?? '')));
            if (
                empty($email) ||
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                str_contains($email, '@guest.') ||
                str_contains($email, '@temporary.')
            ) {
                return null;
            }

            $cols = self::getTableColumns();
            if (empty($cols)) {
                return null;
            }

            $subscriber = self::withTrashed()->where('email', $email)->first();
            if ($subscriber && $subscriber->trashed()) {
                $subscriber->restore();
            }

            if (!$subscriber) {
                $subscriber = new self();
                $subscriber->email = $email;
            }

            $fullName  = trim((string) ($data['name'] ?? ''));
            $firstName = trim((string) ($data['first_name'] ?? ''));
            $lastName  = trim((string) ($data['last_name'] ?? ''));

            if (empty($firstName) && !empty($fullName)) {
                $parts     = explode(' ', $fullName, 2);
                $firstName = $parts[0] ?? '';
                $lastName  = $parts[1] ?? '';
            }

            if (in_array('first_name', $cols, true) && !empty($firstName) && empty($subscriber->first_name)) {
                $subscriber->first_name = $firstName;
            }
            if (in_array('last_name', $cols, true) && !empty($lastName) && empty($subscriber->last_name)) {
                $subscriber->last_name = $lastName;
            }
            if (in_array('name', $cols, true) && !empty($fullName) && empty($subscriber->name)) {
                $subscriber->name = $fullName;
            }
            if (in_array('phone', $cols, true) && !empty($data['phone']) && empty($subscriber->phone)) {
                $subscriber->phone = trim((string) $data['phone']);
            }

            $source = $data['source'] ?? 'direct';
            if (in_array('source', $cols, true) && empty($subscriber->source)) {
                $subscriber->source = $source;
            }
            if (in_array('consent_source', $cols, true) && empty($subscriber->consent_source)) {
                $subscriber->consent_source = $source;
            }
            if (in_array('status', $cols, true) && (empty($subscriber->status) || $subscriber->status !== 'unsubscribed')) {
                $subscriber->status = 'subscribed';
            }
            if (in_array('marketing_consent', $cols, true)) {
                $subscriber->marketing_consent = true;
            }
            if (in_array('consent_at', $cols, true) && empty($subscriber->consent_at)) {
                $subscriber->consent_at = $data['created_at'] ?? now();
            }
            if (in_array('consent_ip', $cols, true) && empty($subscriber->consent_ip)) {
                $subscriber->consent_ip = $data['ip'] ?? request()->ip() ?? '127.0.0.1';
            }
            if (in_array('user_id', $cols, true) && !empty($data['user_id']) && empty($subscriber->user_id)) {
                $subscriber->user_id = $data['user_id'];
            }
            if (!empty($data['created_at']) && !$subscriber->exists) {
                $subscriber->created_at = $data['created_at'];
            }

            $subscriber->save();

            if ($subscriber->wasRecentlyCreated && class_exists(EmailConsentLog::class) && method_exists(EmailConsentLog::class, 'logConsent')) {
                try {
                    EmailConsentLog::logConsent($subscriber->email, 'opt_in', $source, $subscriber->id);
                } catch (\Throwable $e) {
                    // Ignore consent log error safely
                }
            }

            return $subscriber;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Automatically backfill missed emails from recent Orders & Form Submissions
     */
    public static function syncMissedOrdersAndSubmissions(): void
    {
        try {
            if (!Schema::hasTable('email_subscribers')) {
                return;
            }

            // 1. Backfill from Orders Table
            if (Schema::hasTable('orders')) {
                $recentOrders = DB::table('orders')->orderBy('id', 'desc')->limit(100)->get();
                foreach ($recentOrders as $ord) {
                    $ordEmail = $ord->customer_email ?? ($ord->email ?? ($ord->billing_email ?? null));
                    if (!empty($ordEmail) && filter_var(trim((string) $ordEmail), FILTER_VALIDATE_EMAIL)) {
                        self::syncSubscriberSafe([
                            'email'      => $ordEmail,
                            'name'       => $ord->customer_name ?? ($ord->name ?? ''),
                            'phone'      => $ord->customer_phone ?? ($ord->phone ?? null),
                            'source'     => 'checkout',
                            'user_id'    => $ord->user_id ?? null,
                            'ip'         => '127.0.0.1',
                            'created_at' => $ord->created_at ?? now(),
                        ]);
                    }
                }
            }

            // 2. Backfill from Dynamic Form Submissions Table
            if (Schema::hasTable('form_submissions') && Schema::hasTable('form_submission_values')) {
                $submissions = FormSubmission::with(['form', 'values.field'])
                    ->latest('id')
                    ->take(100)
                    ->get();

                foreach ($submissions as $sub) {
                    $subEmail = null;
                    $subName  = '';
                    $subPhone = null;

                    foreach ($sub->values as $v) {
                        $val = trim((string) $v->value);
                        if ($val === '') {
                            continue;
                        }

                        $fType  = strtolower($v->field->type ?? '');
                        $fName  = strtolower($v->field->name ?? '');
                        $fLabel = strtolower($v->field->label ?? '');

                        if (!$subEmail && ($fType === 'email' || str_contains($fName, 'email') || filter_var($val, FILTER_VALIDATE_EMAIL))) {
                            if (filter_var($val, FILTER_VALIDATE_EMAIL)) {
                                $subEmail = strtolower($val);
                            }
                        }

                        if ($subName === '' && (in_array($fName, ['name', 'full_name', 'your_name', 'customer_name']) || str_contains($fLabel, 'name'))) {
                            if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
                                $subName = $val;
                            }
                        }

                        if (!$subPhone && ($fType === 'phone' || str_contains($fName, 'phone') || str_contains($fName, 'mobile') || str_contains($fLabel, 'phone'))) {
                            $subPhone = $val;
                        }
                    }

                    if ($subEmail) {
                        $formIdentifier = strtolower(($sub->form->slug ?? '') . ' ' . ($sub->form->name ?? ''));
                        if (str_contains($formIdentifier, 'coupon') || str_contains($formIdentifier, 'claim') || str_contains($formIdentifier, 'discount')) {
                            $src = 'coupon_claim';
                        } elseif (str_contains($formIdentifier, 'campaign') || str_contains($formIdentifier, 'promo') || str_contains($formIdentifier, 'lead')) {
                            $src = 'campaign_lead';
                        } elseif (str_contains($formIdentifier, 'newsletter') || str_contains($formIdentifier, 'subscribe')) {
                            $src = 'newsletter';
                        } elseif (str_contains($formIdentifier, 'contact') || str_contains($formIdentifier, 'support')) {
                            $src = 'contact_form';
                        } else {
                            $src = 'newsletter';
                        }

                        self::syncSubscriberSafe([
                            'email'      => $subEmail,
                            'name'       => $subName,
                            'phone'      => $subPhone,
                            'source'     => $src,
                            'ip'         => $sub->ip_address ?: '127.0.0.1',
                            'created_at' => $sub->submitted_at ?? ($sub->created_at ?? now()),
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Safely ignore any backfill exception
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (High-Performance Query Filters)
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Only active, consented subscribers
     */
    public function scopeSubscribed($query)
    {
        return $query->where('status', 'subscribed')
                     ->where('marketing_consent', true);
    }

    /**
     * Scope: Eligible for marketing (Not suppressed, consented, and active)
     */
    public function scopeEligibleForMarketing($query)
    {
        return $query->subscribed()
                     ->whereNotExists(function ($subQuery) {
                         $subQuery->selectRaw(1)
                                  ->from('email_suppression_list')
                                  ->whereColumn('email_suppression_list.email', 'email_subscribers.email');
                     });
    }

    /**
     * Scope: Registered e-commerce users only
     */
    public function scopeRegisteredUsers($query)
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Scope: Guest subscribers only
     */
    public function scopeGuests($query)
    {
        return $query->whereNull('user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Linked registered user account (if any)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Historical consent audit logs
     */
    public function consentLogs(): HasMany
    {
        return $this->hasMany(EmailConsentLog::class, 'subscriber_id');
    }

    /**
     * Campaign recipients history
     */
    public function campaignDeliveries(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'subscriber_id');
    }

    /**
     * Automation trigger logs for this subscriber
     */
    public function automationLogs(): HasMany
    {
        return $this->hasMany(EmailAutomationLog::class, 'subscriber_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers & Business Logic
    |--------------------------------------------------------------------------
    */

    /**
     * Get full name or email fallback
     */
    public function getFullNameAttribute(): string
    {
        $fullName = trim("{$this->first_name} {$this->last_name}");
        if ($fullName !== '') {
            return $fullName;
        }
        if (!empty($this->name)) {
            return $this->name;
        }
        return $this->user->name ?? explode('@', $this->email)[0];
    }

    /**
     * Check if subscriber is active and eligible
     */
    public function isEligible(): bool
    {
        return $this->status === 'subscribed' && $this->marketing_consent;
    }

    /**
     * Unsubscribe this subscriber cleanly
     */
    public function unsubscribe(?string $reason = null): bool
    {
        return $this->update([
            'status'             => 'unsubscribed',
            'marketing_consent'  => false,
            'unsubscribed_at'    => now(),
            'unsubscribe_reason' => $reason,
        ]);
    }

    /**
     * Re-subscribe this subscriber
     */
    public function resubscribe(string $source = 'user_action'): bool
    {
        return $this->update([
            'status'             => 'subscribed',
            'marketing_consent'  => true,
            'consent_at'         => now(),
            'consent_source'     => $source,
            'unsubscribed_at'    => null,
            'unsubscribe_reason' => null,
        ]);
    }
}