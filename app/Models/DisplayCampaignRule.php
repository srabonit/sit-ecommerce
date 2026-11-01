<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

class DisplayCampaignRule extends Model
{
    use HasFactory;

    protected $table = 'display_campaign_rules';

    protected $fillable = [
        'ruleable_type',
        'ruleable_id',
        'rule_scope',
        'device_visibility',
        'page_target_type',
        'target_pages',
        'excluded_pages',
        'start_at',
        'end_at',
        'user_type',
        'is_active',
    ];

    protected $casts = [
        'device_visibility' => 'array',
        'target_pages'      => 'array',
        'excluded_pages'    => 'array',
        'start_at'          => 'datetime',
        'end_at'            => 'datetime',
        'is_active'         => 'boolean',
    ];

    /**
     * Polymorphic relation to parent entity (Popup, Widget, etc.)
     */
    public function ruleable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for globally active rules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if date and time scheduling matches current timestamp.
     */
    public function isScheduleValid(): bool
    {
        $now = Carbon::now();

        if ($this->start_at && $now->lt($this->start_at)) {
            return false;
        }

        if ($this->end_at && $now->gt($this->end_at)) {
            return false;
        }

        return true;
    }

    /**
     * Check if device targeting matches.
     * $device: 'desktop', 'tablet', or 'mobile'
     */
    public function matchesDevice(string $device): bool
    {
        if (empty($this->device_visibility)) {
            return true;
        }

        return !empty($this->device_visibility[$device]);
    }

    /**
     * Check if target page/route rules match.
     */
    public function matchesPage(string $currentPath): bool
    {
        $currentPath = trim($currentPath, '/');
        $currentPath = $currentPath === '' ? '/' : $currentPath;

        // Check exclusions first
        if (!empty($this->excluded_pages) && is_array($this->excluded_pages)) {
            foreach ($this->excluded_pages as $pattern) {
                $pattern = trim($pattern, '/');
                if ($this->checkWildcardMatch($pattern, $currentPath)) {
                    return false;
                }
            }
        }

        // Check inclusion rules
        if ($this->page_target_type === 'home_only') {
            return $currentPath === '/';
        }

        if ($this->page_target_type === 'products_only') {
            return str_starts_with($currentPath, 'product/');
        }

        if ($this->page_target_type === 'specific_pages') {
            if (empty($this->target_pages) || !is_array($this->target_pages)) {
                return false;
            }

            foreach ($this->target_pages as $pattern) {
                $pattern = trim($pattern, '/');
                if ($this->checkWildcardMatch($pattern, $currentPath)) {
                    return true;
                }
            }

            return false;
        }

        return true; // 'all' pages
    }

    /**
     * Check if user auth status matches.
     */
    public function matchesUser(bool $isLoggedIn): bool
    {
        if ($this->user_type === 'guest' && $isLoggedIn) {
            return false;
        }

        if ($this->user_type === 'logged_in' && !$isLoggedIn) {
            return false;
        }

        return true;
    }

    /**
     * Internal wildcard route pattern matcher.
     */
    protected function checkWildcardMatch(string $pattern, string $path): bool
    {
        if ($pattern === $path) {
            return true;
        }

        $quotedPattern = preg_quote($pattern, '#');
        $regexPattern = '#^' . str_replace('\*', '.*', $quotedPattern) . '$#i';

        return (bool) preg_match($regexPattern, $path);
    }
}