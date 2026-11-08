<?php

namespace App\Services;

use App\Models\DynamicPopup;
use App\Models\FloatingWidget;
use App\Models\DisplayCampaignRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DisplayRuleEngine
{
    /**
     * Detect current client device category.
     * Returns: 'mobile', 'tablet', or 'desktop'
     */
    public function detectDevice(Request $request): string
    {
        $userAgent = strtolower($request->header('User-Agent', ''));

        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * Get active dynamic popup matching current request, device, schedule & frequency.
     * Synchronized with per_page & global cookie scopes (Hours & Days supported).
     */
    public function getActivePopup(Request $request): ?DynamicPopup
    {
        if (!Schema::hasTable('dynamic_popups')) {
            return null;
        }

        $device = $this->detectDevice($request);
        $currentPath = $request->path();
        $isLoggedIn = Auth::check();

        $popups = DynamicPopup::with('rule')
            ->where('is_active', true)
            ->latest('id')
            ->get();

        foreach ($popups as $popup) {
            $settings = is_array($popup->settings) 
                ? $popup->settings 
                : (json_decode($popup->settings ?? '[]', true) ?: []);

            // 1. Check Cookie frequency (Supports both per_page hash and global cookie)
            $freqScope = $settings['frequency_scope'] ?? 'per_page';
            $pageSlugKey = ($freqScope === 'per_page') ? '_' . md5($currentPath) : '';
            
            $scopedCookie = 'popup_viewed_' . $popup->id . $pageSlugKey;
            $globalCookie = 'popup_viewed_' . $popup->id;

            // যদি নির্দিষ্ট পেজে বা গ্লোবালি অলরেডি দেখা হয়ে থাকে, তবে আর রেন্ডার হবে না
            if ($request->hasCookie($scopedCookie) || $request->hasCookie($globalCookie)) {
                continue;
            }

            // 2. If campaign rule exists, evaluate conditions
            if ($popup->rule) {
                $rule = $popup->rule;

                if (!$rule->is_active || !$rule->isScheduleValid()) {
                    continue;
                }

                if (!$rule->matchesDevice($device)) {
                    continue;
                }

                if (!$rule->matchesPage($currentPath)) {
                    continue;
                }

                if (!$rule->matchesUser($isLoggedIn)) {
                    continue;
                }
            }

            return $popup; // Return first matching active popup
        }

        return null;
    }

    /**
     * Get collection of active floating contact widgets matching current request.
     */
    public function getActiveWidgets(Request $request)
    {
        if (!Schema::hasTable('floating_widgets')) {
            return collect();
        }

        $device = $this->detectDevice($request);
        $currentPath = $request->path();
        $isLoggedIn = Auth::check();

        $widgets = FloatingWidget::with('rule')
            ->where('is_active', true)
            ->orderBy('order_index', 'asc')
            ->get();

        return $widgets->filter(function ($widget) use ($device, $currentPath, $isLoggedIn) {
            if ($widget->rule) {
                $rule = $widget->rule;

                if (!$rule->is_active || !$rule->isScheduleValid()) {
                    return false;
                }

                if (!$rule->matchesDevice($device)) {
                    return false;
                }

                if (!$rule->matchesPage($currentPath)) {
                    return false;
                }

                if (!$rule->matchesUser($isLoggedIn)) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Determine if existing Sticky Footer Bar should display on current route.
     * Safely preserves existing settings without modifying DB.
     */
    public function shouldShowStickyFooterBar(Request $request): bool
    {
        // 1. Check existing setting from database
        $isEnabled = false;
        if (Schema::hasTable('settings')) {
            $setting = DB::table('settings')->where('key', 'footer_sticky_bar_enable')->first();
            $isEnabled = $setting && in_array(strtolower($setting->value), ['1', 'true', 'on', 'yes']);
        }

        if (!$isEnabled) {
            return false;
        }

        // 2. Safety Priority: Never conflict with Product Sticky Cart on product details page
        $currentPath = $request->path();
        if (str_starts_with($currentPath, 'product/')) {
            // If sticky cart is active on product page, suppress general sticky footer bar to avoid collision
            return false;
        }

        // 3. Check advanced rule targeting if exists
        if (Schema::hasTable('display_campaign_rules')) {
            $rule = DisplayCampaignRule::where('rule_scope', 'sticky_footer_bar')
                ->where('is_active', true)
                ->first();

            if ($rule) {
                $device = $this->detectDevice($request);
                $isLoggedIn = Auth::check();

                if (!$rule->isScheduleValid() ||
                    !$rule->matchesDevice($device) ||
                    !$rule->matchesPage($currentPath) ||
                    !$rule->matchesUser($isLoggedIn)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Calculate dynamic bottom CSS offset to prevent element collisions.
     */
    public function getPositionOffsets(Request $request): array
    {
        $hasStickyFooter = $this->shouldShowStickyFooterBar($request);
        $isProductPage = str_starts_with($request->path(), 'product/');

        // Default base offsets
        $baseBottom = 20;

        if ($hasStickyFooter) {
            $baseBottom += 55; // Height of sticky footer + safety margin
        } elseif ($isProductPage) {
            $baseBottom += 70; // Height of product sticky cart + safety margin
        }

        return [
            'has_sticky_footer' => $hasStickyFooter,
            'is_product_page'   => $isProductPage,
            'calculated_bottom' => $baseBottom,
        ];
    }
}