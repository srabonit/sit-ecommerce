<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $table = 'payment_transactions';

    // Transaction Source Types
    public const SOURCE_CHECKOUT = 'checkout';
    public const SOURCE_POS_CASH = 'pos_cash';
    public const SOURCE_COD      = 'cod';
    public const SOURCE_MANUAL   = 'manual';
    public const SOURCE_GATEWAY  = 'gateway';

    // Verification Statuses
    public const STATUS_PENDING_VERIFICATION = 'pending_verification';
    public const STATUS_CHECKED              = 'checked';
    public const STATUS_APPROVED             = 'approved';
    public const STATUS_PAID                 = 'paid';
    public const STATUS_PARTIALLY_PAID       = 'partially_paid';
    public const STATUS_UNMATCHED            = 'unmatched';
    public const STATUS_FAKE                 = 'fake';
    public const STATUS_UNDER_REVIEW         = 'under_review';
    public const STATUS_FAILED               = 'failed';
    public const STATUS_CANCELLED            = 'cancelled';
    public const STATUS_REFUNDED             = 'refunded';

    protected $fillable = [
        'transaction_uid',
        'order_id',
        'order_number',
        'invoice_no',
        'branch_id',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'source_type',
        'payment_method',
        'payment_provider',
        'sender_number',
        'transaction_id',
        'normalized_trx_id',
        'is_duplicate_flagged',
        'duplicate_of_transaction_id',
        'duplicate_of_order_id',
        'expected_amount',
        'submitted_amount',
        'verified_amount',
        'refunded_amount',
        'delivery_charge',
        'cod_amount',
        'screenshot_path',
        'verification_status',
        'cod_settlement_status',
        'created_by',
        'checked_by',
        'checked_at',
        'approved_by',
        'approved_at',
        'verified_by',
        'verified_at',
        'updated_by',
        'admin_remarks',
        'rejection_reason',
        'meta_data',
        'submitted_at',
        'deleted_at',
    ];

    protected $casts = [
        'is_duplicate_flagged' => 'boolean',
        'expected_amount'      => 'decimal:2',
        'submitted_amount'     => 'decimal:2',
        'verified_amount'      => 'decimal:2',
        'refunded_amount'      => 'decimal:2',
        'delivery_charge'      => 'decimal:2',
        'cod_amount'           => 'decimal:2',
        'meta_data'            => 'array',
        'checked_at'           => 'datetime',
        'approved_at'          => 'datetime',
        'verified_at'          => 'datetime',
        'submitted_at'         => 'datetime',
        'deleted_at'           => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->transaction_uid)) {
                $model->transaction_uid = self::generateUniqueUid();
            }

            if (!empty($model->transaction_id) && empty($model->normalized_trx_id)) {
                $model->normalized_trx_id = self::normalizeTrxId($model->transaction_id);
            }

            if (empty($model->submitted_at)) {
                $model->submitted_at = now();
            }
        });

        static::updating(function (self $model) {
            if ($model->isDirty('transaction_id')) {
                $model->normalized_trx_id = self::normalizeTrxId($model->transaction_id);
            }
        });
    }

    public static function generateUniqueUid(): string
    {
        return 'TXN-' . date('Ymd') . '-' . strtoupper(Str::random(6));
    }

    public static function normalizeTrxId(?string $trxId): ?string
    {
        if (empty($trxId)) {
            return null;
        }
        $cleaned = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', trim($trxId)));
        return $cleaned !== '' ? $cleaned : null;
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id')->withoutGlobalScopes();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withoutGlobalScopes();
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by')->withoutGlobalScopes();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by')->withoutGlobalScopes();
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by')->withoutGlobalScopes();
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withoutGlobalScopes();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(PaymentVerificationHistory::class, 'payment_transaction_id')->latest('id');
    }

    // =========================================================================
    // ACCESSORS & HELPERS
    // =========================================================================

    public function getScreenshotUrlAttribute(): ?string
    {
        if (empty($this->screenshot_path)) {
            return null;
        }

        $path = trim($this->screenshot_path);
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $clean = ltrim(str_replace(['public/'], '', $path), '/');
        if (file_exists(public_path($clean))) {
            return asset($clean);
        }
        if (file_exists(public_path('uploads/' . $clean))) {
            return asset('uploads/' . $clean);
        }
        if (file_exists(public_path('storage/' . $clean))) {
            return asset('storage/' . $clean);
        }

        return asset($clean);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->verification_status) {
            self::STATUS_PENDING_VERIFICATION => 'Pending Verification',
            self::STATUS_CHECKED              => 'Admin Checked',
            self::STATUS_APPROVED             => 'Approved',
            self::STATUS_PAID                 => 'Paid / Received',
            self::STATUS_PARTIALLY_PAID       => 'Partially Paid',
            self::STATUS_UNMATCHED            => 'Unmatched',
            self::STATUS_FAKE                 => 'Fake / Rejected',
            self::STATUS_UNDER_REVIEW         => 'Under Review',
            self::STATUS_FAILED               => 'Failed',
            self::STATUS_CANCELLED            => 'Cancelled',
            self::STATUS_REFUNDED             => 'Refunded',
            default                           => ucwords(str_replace('_', ' ', (string) $this->verification_status)),
        };
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source_type) {
            self::SOURCE_CHECKOUT => 'Checkout Request',
            self::SOURCE_POS_CASH => 'POS Terminal',
            self::SOURCE_COD      => 'COD Order',
            self::SOURCE_MANUAL   => 'Manual Entry',
            self::SOURCE_GATEWAY  => 'Online Gateway',
            default               => strtoupper((string) $this->source_type),
        };
    }
}