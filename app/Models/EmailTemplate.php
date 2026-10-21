<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'email_templates';

    protected $fillable = [
        'name',
        'category',
        'subject',
        'builder_data',
        'html_content',
        'plain_text',
        'thumbnail',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'builder_data' => 'array',
        'is_active'    => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes (High-Performance Query Filters)
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: Only active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by template category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Template Creator (Admin / Staff User)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Campaigns using this template
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(EmailCampaign::class, 'template_id');
    }

    /**
     * Automation steps using this template
     */
    public function automationSteps(): HasMany
    {
        return $this->hasMany(EmailAutomationStep::class, 'template_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers & Dynamic Variable Parser
    |--------------------------------------------------------------------------
    */

    /**
     * Render the template with dynamic personalized variables
     *
     * Supported Tags:
     * {{customer_name}}, {{first_name}}, {{email}}, {{unsubscribe_url}},
     * {{coupon_code}}, {{cart_recovery_url}}, {{app_name}}, {{year}}
     *
     * @param array $variables
     * @return string
     */
    public function render(array $variables = []): string
    {
        $content = $this->html_content ?? '';

        // Default System Fallbacks
        $defaults = [
            'app_name' => config('app.name', 'E-commerce'),
            'app_url'  => config('app.url', url('/')),
            'year'     => date('Y'),
        ];

        $merged = array_merge($defaults, $variables);

        foreach ($merged as $key => $value) {
            if (is_scalar($value) || is_null($value)) {
                $valStr = (string)$value;
                $content = str_replace(
                    ['{{' . $key . '}}', '{{ ' . $key . ' }}'],
                    $valStr,
                    $content
                );
            }
        }

        return $content;
    }

    /**
     * Generate plain text version if missing
     */
    public function getCleanPlainTextAttribute(): string
    {
        if (!empty($this->plain_text)) {
            return $this->plain_text;
        }

        return trim(strip_tags(preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $this->html_content ?? '')));
    }
}