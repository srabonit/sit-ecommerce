<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ThemeExtendedColorController extends Controller
{
    /**
     * Get all extended module keys grouped by section
     */
    protected function getGroupedKeys(): array
    {
        return [
            'user_dashboard' => [
                // Layout & Sidebar
                'color_user_page_bg',
                'color_user_sidebar_bg',
                'color_user_sidebar_border',
                'color_user_sidebar_text',
                'color_user_sidebar_hover_bg',
                'color_user_sidebar_hover_text',
                'color_user_sidebar_active_bg',
                'color_user_sidebar_active_text',

                // Cards, Stats & Typography
                'color_user_card_bg',
                'color_user_card_border',
                'color_user_card_title',
                'color_user_card_text',
                'color_user_stat_card_bg',
                'color_user_stat_card_border',
                'color_user_stat_number',
                'color_user_stat_label',
                'color_user_stat_icon',

                // Tables & Lists (Orders, Addresses, Coupons, Wishlist)
                'color_user_table_header_bg',
                'color_user_table_header_text',
                'color_user_table_border',
                'color_user_table_text',

                // Status Badges (Pending, Completed, Cancelled)
                'color_user_badge_pending_bg',
                'color_user_badge_pending_text',
                'color_user_badge_completed_bg',
                'color_user_badge_completed_text',
                'color_user_badge_cancelled_bg',
                'color_user_badge_cancelled_text',

                // Forms, Buttons & Inputs (Profile, Support, Track Order)
                'color_user_btn_primary_bg',
                'color_user_btn_primary_text',
                'color_user_btn_primary_hover',
                'color_user_input_bg',
                'color_user_input_border',
                'color_user_input_focus_border',
            ],
            'emails' => [
                // Email Notifications, Orders & Digital Delivery
                'color_email_site_bg',
                'color_email_card_bg',
                'color_email_card_border',
                'color_email_header_bg',
                'color_email_header_text',
                'color_email_title',
                'color_email_text',
                'color_email_price',
                'color_email_btn_bg',
                'color_email_btn_text',
                'color_email_btn_hover',
                'color_email_badge_bg',
                'color_email_badge_text',
                'color_email_table_th_bg',
                'color_email_table_th_text',
                'color_email_table_border',
                'color_email_footer_bg',
                'color_email_footer_text',

                // Unsubscribe & Newsletter Preferences
                'color_unsub_page_bg',
                'color_unsub_card_bg',
                'color_unsub_card_border',
                'color_unsub_title',
                'color_unsub_text',
                'color_unsub_input_bg',
                'color_unsub_input_border',
                'color_unsub_input_focus_border',
                'color_unsub_btn_bg',
                'color_unsub_btn_hover',
                'color_unsub_btn_text',
                'color_unsub_cancel_btn_bg',
                'color_unsub_cancel_btn_text',

                // Dynamic Form Notifications & Submissions
                'color_form_mail_header_bg',
                'color_form_mail_primary',
                'color_form_mail_btn_hover'
            ],
            'cart' => [
                'color_cart_page_bg', 'color_cart_panel_bg', 'color_cart_panel_border',
                'color_cart_item_bg', 'color_cart_item_border', 'color_cart_item_border_hover',
                'color_cart_item_img_bg', 'color_cart_item_img_border',
                'color_cart_item_title', 'color_cart_item_title_hover', 'color_cart_item_price',
                'color_cart_variant_bg', 'color_cart_variant_text', 'color_cart_variant_border',
                'color_cart_sku_text',
                'color_cart_qty_btn_bg', 'color_cart_qty_btn_text', 'color_cart_qty_btn_border',
                'color_cart_qty_btn_hover_bg', 'color_cart_qty_btn_hover_text',
                'color_cart_remove_btn', 'color_cart_remove_btn_hover',
                'color_cart_checkout_btn_bg', 'color_cart_checkout_btn_text', 'color_cart_checkout_btn_hover',
                'color_cart_empty_icon', 'color_cart_empty_btn_bg', 'color_cart_empty_btn_text', 'color_cart_empty_btn_hover_bg'
            ],
            'checkout' => [
                'color_checkout_page_bg', 'color_checkout_card_bg', 'color_checkout_card_border',
                'color_checkout_heading', 'color_checkout_text', 'color_checkout_label',
                'color_checkout_input_bg', 'color_checkout_input_border', 'color_checkout_input_focus_border',
                'color_checkout_paybox_bg', 'color_checkout_paybox_border',
                'color_checkout_paybox_active_bg', 'color_checkout_paybox_active_border',
                'color_checkout_total_price',
                'color_checkout_btn_bg', 'color_checkout_btn_text', 'color_checkout_btn_hover'
            ],
            'token' => [
                'color_token_page_bg', 'color_token_success_icon_bg', 'color_token_success_icon_text',
                'color_token_heading', 'color_token_subtext',
                'color_token_total_card_bg', 'color_token_total_card_border', 'color_token_total_card_label', 'color_token_total_price',
                'color_token_download_btn_bg', 'color_token_download_btn_text', 'color_token_download_btn_hover',
                'color_token_continue_btn_bg', 'color_token_continue_btn_text', 'color_token_continue_btn_border', 'color_token_continue_btn_hover',
                'color_token_memo_bg', 'color_token_memo_border', 'color_token_memo_text', 'color_token_memo_divider',
                'color_token_digital_box_bg', 'color_token_digital_box_border'
            ],
            'components' => [
                'color_cat_card_bg', 'color_cat_card_border', 'color_cat_card_border_hover',
                'color_cat_card_title', 'color_cat_card_title_hover', 'color_cat_card_count',
                'color_section_title', 'color_section_link', 'color_section_link_hover',
                'color_page_sidebar_bg', 'color_page_sidebar_border', 'color_page_sidebar_header_text',
                'color_page_sidebar_text', 'color_blog_search_btn_bg', 'color_blog_search_btn_text'
            ],
            'auth' => [
                'color_auth_page_bg', 'color_auth_card_bg', 'color_auth_card_border',
                'color_auth_heading', 'color_auth_text',
                'color_auth_input_bg', 'color_auth_input_border', 'color_auth_input_focus_border',
                'color_auth_btn_bg', 'color_auth_btn_text', 'color_auth_btn_hover',
                'color_auth_link', 'color_auth_link_hover',
                'color_common_btn_bg', 'color_common_btn_text'
            ],
            'hovers' => [
                'color_ftr_social_icon', 'color_ftr_social_bg', 'color_ftr_social_border',
                'color_ftr_social_hover_icon', 'color_ftr_social_hover_bg', 'color_ftr_social_hover_border',
                'color_pdp_qty_btn_bg', 'color_pdp_qty_btn_text', 'color_pdp_qty_border',
                'color_pdp_qty_btn_hover_bg', 'color_pdp_qty_btn_hover_text',
                'color_pdp_buynow_bg', 'color_pdp_buynow_text', 'color_pdp_buynow_border', 'color_pdp_buynow_icon',
                'color_pdp_buynow_hover_bg', 'color_pdp_buynow_hover_text', 'color_pdp_buynow_hover_border', 'color_pdp_buynow_hover_icon',
                'color_pdp_cart_bg', 'color_pdp_cart_text', 'color_pdp_cart_border', 'color_pdp_cart_icon',
                'color_pdp_cart_hover_bg', 'color_pdp_cart_hover_text', 'color_pdp_cart_hover_border', 'color_pdp_cart_hover_icon',
                'color_pdp_wa_bg', 'color_pdp_wa_text', 'color_pdp_wa_border', 'color_pdp_wa_icon',
                'color_pdp_wa_hover_bg', 'color_pdp_wa_hover_text', 'color_pdp_wa_hover_border', 'color_pdp_wa_hover_icon'
            ]
        ];
    }

    /**
     * Default color values for extended modules
     */
    protected function getDefaults(): array
    {
        return [
            // User Dashboard Suite Defaults
            'color_user_page_bg'                  => '#f8fafc',
            'color_user_sidebar_bg'               => '#ffffff',
            'color_user_sidebar_border'           => '#edf2f7',
            'color_user_sidebar_text'             => '#475569',
            'color_user_sidebar_hover_bg'         => '#f1f5f9',
            'color_user_sidebar_hover_text'       => '#2563eb',
            'color_user_sidebar_active_bg'        => '#eff6ff',
            'color_user_sidebar_active_text'      => '#2563eb',

            'color_user_card_bg'                  => '#ffffff',
            'color_user_card_border'              => '#edf2f7',
            'color_user_card_title'               => '#0f172a',
            'color_user_card_text'                => '#64748b',

            'color_user_stat_card_bg'             => '#ffffff',
            'color_user_stat_card_border'         => '#edf2f7',
            'color_user_stat_number'              => '#0f172a',
            'color_user_stat_label'               => '#64748b',
            'color_user_stat_icon'                => '#2563eb',

            'color_user_table_header_bg'          => '#f8fafc',
            'color_user_table_header_text'        => '#475569',
            'color_user_table_border'             => '#e2e8f0',
            'color_user_table_text'               => '#1e293b',

            'color_user_badge_pending_bg'         => '#fef3c7',
            'color_user_badge_pending_text'       => '#b45309',
            'color_user_badge_completed_bg'       => '#ecfdf5',
            'color_user_badge_completed_text'     => '#047857',
            'color_user_badge_cancelled_bg'       => '#fee2e2',
            'color_user_badge_cancelled_text'     => '#b91c1c',

            'color_user_btn_primary_bg'           => '#2563eb',
            'color_user_btn_primary_text'         => '#ffffff',
            'color_user_btn_primary_hover'        => '#1d4ed8',
            'color_user_input_bg'                 => '#ffffff',
            'color_user_input_border'             => '#cbd5e1',
            'color_user_input_focus_border'       => '#2563eb',

            // Email & Notification Templates Defaults
            'color_email_site_bg'                 => '#f8fafc',
            'color_email_card_bg'                 => '#ffffff',
            'color_email_card_border'             => '#e2e8f0',
            'color_email_header_bg'               => '#0f172a',
            'color_email_header_text'             => '#ffffff',
            'color_email_title'                   => '#0f172a',
            'color_email_text'                    => '#475569',
            'color_email_price'                   => '#2563eb',
            'color_email_btn_bg'                  => '#2563eb',
            'color_email_btn_text'                => '#ffffff',
            'color_email_btn_hover'               => '#1d4ed8',
            'color_email_badge_bg'                => '#f1f5f9',
            'color_email_badge_text'              => '#1e293b',
            'color_email_table_th_bg'             => '#f1f5f9',
            'color_email_table_th_text'           => '#475569',
            'color_email_table_border'            => '#e2e8f0',
            'color_email_footer_bg'               => '#f8fafc',
            'color_email_footer_text'             => '#94a3b8',

            // Unsubscribe & Newsletter Preferences Defaults
            'color_unsub_page_bg'                 => '#f8fafc',
            'color_unsub_card_bg'                 => '#ffffff',
            'color_unsub_card_border'             => '#e2e8f0',
            'color_unsub_title'                   => '#0f172a',
            'color_unsub_text'                    => '#64748b',
            'color_unsub_input_bg'                => '#ffffff',
            'color_unsub_input_border'            => '#cbd5e1',
            'color_unsub_input_focus_border'      => '#2563eb',
            'color_unsub_btn_bg'                  => '#dc2626',
            'color_unsub_btn_hover'               => '#b91c1c',
            'color_unsub_btn_text'                => '#ffffff',
            'color_unsub_cancel_btn_bg'           => '#ffffff',
            'color_unsub_cancel_btn_text'         => '#64748b',

            // Dynamic Form Notifications & Submissions Defaults
            'color_form_mail_header_bg'           => '#0f172a',
            'color_form_mail_primary'             => '#2563eb',
            'color_form_mail_btn_hover'           => '#1d4ed8',

            // Cart & Drawer Defaults
            'color_cart_page_bg'                  => '#f8fafc',
            'color_cart_panel_bg'                 => '#ffffff',
            'color_cart_panel_border'             => '#edf2f7',
            'color_cart_item_bg'                  => '#ffffff',
            'color_cart_item_border'              => '#edf2f7',
            'color_cart_item_border_hover'        => '#cbd5e1',
            'color_cart_item_img_bg'              => '#f8fafc',
            'color_cart_item_img_border'          => '#e2e8f0',
            'color_cart_item_title'               => '#0f172a',
            'color_cart_item_title_hover'         => '#2563eb',
            'color_cart_item_price'               => '#0f172a',
            'color_cart_variant_bg'               => '#f8fafc',
            'color_cart_variant_text'             => '#64748b',
            'color_cart_variant_border'           => '#e2e8f0',
            'color_cart_sku_text'                 => '#94a3b8',
            'color_cart_qty_btn_bg'               => '#f8fafc',
            'color_cart_qty_btn_text'             => '#334155',
            'color_cart_qty_btn_border'           => '#e2e8f0',
            'color_cart_qty_btn_hover_bg'         => '#e2e8f0',
            'color_cart_qty_btn_hover_text'       => '#0f172a',
            'color_cart_remove_btn'               => '#ef4444',
            'color_cart_remove_btn_hover'         => '#dc2626',
            'color_cart_checkout_btn_bg'          => '#2563eb',
            'color_cart_checkout_btn_text'        => '#ffffff',
            'color_cart_checkout_btn_hover'       => '#1d4ed8',
            'color_cart_empty_icon'               => '#94a3b8',
            'color_cart_empty_btn_bg'             => '#2563eb',
            'color_cart_empty_btn_text'           => '#ffffff',
            'color_cart_empty_btn_hover_bg'       => '#1d4ed8',

            // Checkout Page Defaults
            'color_checkout_page_bg'              => '#f8fafc',
            'color_checkout_card_bg'              => '#ffffff',
            'color_checkout_card_border'          => '#edf2f7',
            'color_checkout_heading'              => '#0f172a',
            'color_checkout_text'                 => '#334155',
            'color_checkout_label'                => '#0f172a',
            'color_checkout_input_bg'             => '#ffffff',
            'color_checkout_input_border'         => '#cbd5e1',
            'color_checkout_input_focus_border'   => '#2563eb',
            'color_checkout_paybox_bg'            => '#ffffff',
            'color_checkout_paybox_border'        => '#e2e8f0',
            'color_checkout_paybox_active_bg'     => '#eff6ff',
            'color_checkout_paybox_active_border' => '#2563eb',
            'color_checkout_total_price'          => '#2563eb',
            'color_checkout_btn_bg'               => '#2563eb',
            'color_checkout_btn_text'             => '#ffffff',
            'color_checkout_btn_hover'            => '#1d4ed8',

            // Order Confirmation / Token Defaults
            'color_token_page_bg'                 => '#f1f5f9',
            'color_token_success_icon_bg'         => '#16a34a',
            'color_token_success_icon_text'       => '#ffffff',
            'color_token_heading'                 => '#0f172a',
            'color_token_subtext'                 => '#64748b',
            'color_token_total_card_bg'           => '#ffffff',
            'color_token_total_card_border'       => '#cbd5e1',
            'color_token_total_card_label'        => '#64748b',
            'color_token_total_price'             => '#0f172a',
            'color_token_download_btn_bg'         => '#0f172a',
            'color_token_download_btn_text'       => '#ffffff',
            'color_token_download_btn_hover'      => '#1e293b',
            'color_token_continue_btn_bg'         => '#ffffff',
            'color_token_continue_btn_text'       => '#475569',
            'color_token_continue_btn_border'     => '#cbd5e1',
            'color_token_continue_btn_hover'      => '#f8fafc',
            'color_token_memo_bg'                 => '#ffffff',
            'color_token_memo_border'             => '#94a3b8',
            'color_token_memo_text'               => '#0f172a',
            'color_token_memo_divider'            => '#475569',
            'color_token_digital_box_bg'          => '#f8fafc',
            'color_token_digital_box_border'      => '#2563eb',

            // Components & Widgets Defaults
            'color_cat_card_bg'                   => '#f8fafc',
            'color_cat_card_border'               => '#edf2f7',
            'color_cat_card_border_hover'         => '#2563eb',
            'color_cat_card_title'                => '#0f172a',
            'color_cat_card_title_hover'          => '#2563eb',
            'color_cat_card_count'                => '#64748b',
            'color_section_title'                 => '#0f172a',
            'color_section_link'                  => '#2563eb',
            'color_section_link_hover'            => '#1d4ed8',
            'color_page_sidebar_bg'               => '#ffffff',
            'color_page_sidebar_border'           => '#edf2f7',
            'color_page_sidebar_header_text'      => '#0f172a',
            'color_page_sidebar_text'             => '#475569',
            'color_blog_search_btn_bg'            => '#2563eb',
            'color_blog_search_btn_text'          => '#ffffff',

            // Auth & Error Defaults
            'color_auth_page_bg'                  => '#f8fafc',
            'color_auth_card_bg'                  => '#ffffff',
            'color_auth_card_border'              => '#edf2f7',
            'color_auth_heading'                  => '#0f172a',
            'color_auth_text'                     => '#64748b',
            'color_auth_input_bg'                 => '#ffffff',
            'color_auth_input_border'             => '#cbd5e1',
            'color_auth_input_focus_border'       => '#2563eb',
            'color_auth_btn_bg'                   => '#2563eb',
            'color_auth_btn_text'                 => '#ffffff',
            'color_auth_btn_hover'                => '#1d4ed8',
            'color_auth_link'                     => '#2563eb',
            'color_auth_link_hover'               => '#1d4ed8',
            'color_common_btn_bg'                 => '#2563eb',
            'color_common_btn_text'               => '#ffffff',

            // Product Action Hovers, WhatsApp & Footer Social Defaults
            'color_ftr_social_icon'               => '#ffffff',
            'color_ftr_social_bg'                 => '#1e293b',
            'color_ftr_social_border'             => '#334155',
            'color_ftr_social_hover_icon'         => '#ffffff',
            'color_ftr_social_hover_bg'           => '#2563eb',
            'color_ftr_social_hover_border'       => '#2563eb',
            'color_pdp_qty_btn_bg'                => '#f8fafc',
            'color_pdp_qty_btn_text'              => '#334155',
            'color_pdp_qty_border'                => '#cbd5e1',
            'color_pdp_qty_btn_hover_bg'          => '#e2e8f0',
            'color_pdp_qty_btn_hover_text'        => '#0f172a',
            'color_pdp_buynow_bg'                 => '#0dcaf0',
            'color_pdp_buynow_text'               => '#ffffff',
            'color_pdp_buynow_border'             => '#0dcaf0',
            'color_pdp_buynow_icon'               => '#ffffff',
            'color_pdp_buynow_hover_bg'           => '#0bb5d8',
            'color_pdp_buynow_hover_text'         => '#ffffff',
            'color_pdp_buynow_hover_border'       => '#0bb5d8',
            'color_pdp_buynow_hover_icon'         => '#ffffff',
            'color_pdp_cart_bg'                   => '#ffc107',
            'color_pdp_cart_text'                 => '#212529',
            'color_pdp_cart_border'               => '#ffc107',
            'color_pdp_cart_icon'                 => '#212529',
            'color_pdp_cart_hover_bg'             => '#e0a800',
            'color_pdp_cart_hover_text'           => '#212529',
            'color_pdp_cart_hover_border'         => '#e0a800',
            'color_pdp_cart_hover_icon'           => '#212529',
            'color_pdp_wa_bg'                     => '#25D366',
            'color_pdp_wa_text'                   => '#ffffff',
            'color_pdp_wa_border'                 => '#25D366',
            'color_pdp_wa_icon'                   => '#ffffff',
            'color_pdp_wa_hover_bg'               => '#128C7E',
            'color_pdp_wa_hover_text'             => '#ffffff',
            'color_pdp_wa_hover_border'           => '#128C7E',
            'color_pdp_wa_hover_icon'             => '#ffffff',
        ];
    }

    public function index()
    {
        $allSettings = DB::table('settings')->pluck('value', 'key')->toArray();
        $grouped = $this->getGroupedKeys();
        $defaults = $this->getDefaults();

        $allKeys = array_merge(...array_values($grouped));
        $colors = [];

        foreach ($allKeys as $key) {
            $colors[$key] = $allSettings[$key] ?? ($defaults[$key] ?? '#ffffff');
        }

        return view('admin.theme.extended_colors', compact('colors'));
    }

    public function update(Request $request)
    {
        $grouped = $this->getGroupedKeys();
        $allKeys = array_merge(...array_values($grouped));

        // Scoped Section Resets
        if ($request->input('reset_user_dashboard') === '1') {
            DB::table('settings')->whereIn('key', $grouped['user_dashboard'])->delete();
            $this->flushCaches();
            return back()->with('success', 'User Dashboard suite colors restored to default.');
        }

        if ($request->input('reset_emails') === '1') {
            DB::table('settings')->whereIn('key', $grouped['emails'])->delete();
            $this->flushCaches();
            return back()->with('success', 'Email & Notification template colors restored to default.');
        }

        if ($request->input('reset_cart') === '1') {
            DB::table('settings')->whereIn('key', $grouped['cart'])->delete();
            $this->flushCaches();
            return back()->with('success', 'Shopping Cart & Drawer colors restored to default.');
        }

        if ($request->input('reset_checkout') === '1') {
            DB::table('settings')->whereIn('key', $grouped['checkout'])->delete();
            $this->flushCaches();
            return back()->with('success', 'Checkout page colors restored to default.');
        }

        if ($request->input('reset_token') === '1') {
            DB::table('settings')->whereIn('key', $grouped['token'])->delete();
            $this->flushCaches();
            return back()->with('success', 'Order Confirmation & Receipt Token colors restored to default.');
        }

        if ($request->input('reset_components') === '1') {
            DB::table('settings')->whereIn('key', $grouped['components'])->delete();
            $this->flushCaches();
            return back()->with('success', 'Category Card, Section Header & Widget colors restored to default.');
        }

        if ($request->input('reset_auth') === '1') {
            DB::table('settings')->whereIn('key', $grouped['auth'])->delete();
            $this->flushCaches();
            return back()->with('success', 'Authentication & Error page colors restored to default.');
        }

        if ($request->input('reset_hovers') === '1') {
            DB::table('settings')->whereIn('key', $grouped['hovers'])->delete();
            $this->flushCaches();
            return back()->with('success', 'Product Action Hovers, WhatsApp & Social icon colors restored to default.');
        }

        if ($request->input('reset_all_extended') === '1') {
            DB::table('settings')->whereIn('key', $allKeys)->delete();
            $this->flushCaches();
            return back()->with('success', 'All extended module colors restored to system defaults.');
        }

        foreach ($allKeys as $key) {
            if ($request->filled($key)) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => trim($request->input($key)), 'updated_at' => now()]
                );
            }
        }

        $this->flushCaches();

        return back()->with('success', 'Extended module colors updated successfully.');
    }

    protected function flushCaches(): void
    {
        Cache::forget('global_settings_cache');
        Cache::forget('product_card_settings_cache');
        Cache::forget('product_detail_theme_settings_cache');
        cache()->forget('global_settings_cache');
        cache()->forget('product_card_settings_cache');
        cache()->forget('product_detail_theme_settings_cache');
    }
}