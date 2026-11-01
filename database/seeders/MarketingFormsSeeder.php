<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarketingFormsSeeder extends Seeder
{
    /**
     * Run the database seeds for Marketing Forms.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            // =========================================================================
            // 1. NEWSLETTER SUBSCRIBER FORM
            // Shortcode: [form slug="newsletter"]
            // =========================================================================
            $newsletterForm = Form::firstOrCreate(
                ['slug' => 'newsletter'],
                [
                    'name'               => 'Newsletter Subscription',
                    'description'        => 'Subscribe to our newsletter to receive the latest updates, special deals, and exclusive offers.',
                    'success_message'    => 'Thank you for subscribing! Check your inbox soon for updates.',
                    'is_active'          => true,
                    'send_admin_email'   => false,
                    'send_user_email'    => false,
                ]
            );

            // Newsletter Field: Email
            FormField::firstOrCreate(
                [
                    'form_id' => $newsletterForm->id,
                    'name'    => 'email',
                ],
                [
                    'label'         => 'Email Address',
                    'type'          => 'email',
                    'placeholder'   => 'Enter your email address...',
                    'is_required'   => true,
                    'column_width'  => 12,
                    'sort_order'    => 1,
                    'is_active'     => true,
                ]
            );

            // =========================================================================
            // 2. CAMPAIGN / PROMOTIONAL LEAD FORM
            // Shortcode: [form slug="campaign-lead"]
            // =========================================================================
            $campaignForm = Form::firstOrCreate(
                ['slug' => 'campaign-lead'],
                [
                    'name'               => 'Exclusive Campaign Registration',
                    'description'        => 'Register for our special seasonal campaign to unlock VIP perks, priority support, and mega discounts.',
                    'success_message'    => 'Congratulations! You have successfully registered for our campaign.',
                    'is_active'          => true,
                    'send_admin_email'   => false,
                    'send_user_email'    => false,
                ]
            );

            // Campaign Fields: Name, Email, Phone, Note
            FormField::firstOrCreate(
                [
                    'form_id' => $campaignForm->id,
                    'name'    => 'name',
                ],
                [
                    'label'         => 'Full Name',
                    'type'          => 'text',
                    'placeholder'   => 'Enter your full name',
                    'is_required'   => true,
                    'column_width'  => 6,
                    'sort_order'    => 1,
                    'is_active'     => true,
                ]
            );

            FormField::firstOrCreate(
                [
                    'form_id' => $campaignForm->id,
                    'name'    => 'email',
                ],
                [
                    'label'         => 'Email Address',
                    'type'          => 'email',
                    'placeholder'   => 'Enter your email address',
                    'is_required'   => true,
                    'column_width'  => 6,
                    'sort_order'    => 2,
                    'is_active'     => true,
                ]
            );

            FormField::firstOrCreate(
                [
                    'form_id' => $campaignForm->id,
                    'name'    => 'phone',
                ],
                [
                    'label'         => 'Phone / WhatsApp Number',
                    'type'          => 'phone',
                    'placeholder'   => '01700000000',
                    'is_required'   => true,
                    'column_width'  => 12,
                    'sort_order'    => 3,
                    'is_active'     => true,
                ]
            );

            FormField::firstOrCreate(
                [
                    'form_id' => $campaignForm->id,
                    'name'    => 'campaign_note',
                ],
                [
                    'label'         => 'Interested Products / Note (Optional)',
                    'type'          => 'textarea',
                    'placeholder'   => 'What products are you most interested in?',
                    'is_required'   => false,
                    'column_width'  => 12,
                    'sort_order'    => 4,
                    'is_active'     => true,
                ]
            );

            // =========================================================================
            // 3. COUPON CLAIM / DISCOUNT VOUCHER FORM
            // Shortcode: [form slug="claim-coupon"]
            // =========================================================================
            $couponForm = Form::firstOrCreate(
                ['slug' => 'claim-coupon'],
                [
                    'name'               => 'Claim Your Discount Coupon',
                    'description'        => 'Enter your email below to instantly receive an exclusive promo coupon code for your next order.',
                    'success_message'    => 'Awesome! Here is your exclusive discount coupon code:',
                    'is_active'          => true,
                    'send_admin_email'   => false,
                    'send_user_email'    => false,
                ]
            );

            // Coupon Form Fields: Name, Email
            FormField::firstOrCreate(
                [
                    'form_id' => $couponForm->id,
                    'name'    => 'name',
                ],
                [
                    'label'         => 'Your Name',
                    'type'          => 'text',
                    'placeholder'   => 'Enter your name',
                    'is_required'   => false,
                    'column_width'  => 6,
                    'sort_order'    => 1,
                    'is_active'     => true,
                ]
            );

            FormField::firstOrCreate(
                [
                    'form_id' => $couponForm->id,
                    'name'    => 'email',
                ],
                [
                    'label'         => 'Email Address',
                    'type'          => 'email',
                    'placeholder'   => 'Enter your email to receive discount',
                    'is_required'   => true,
                    'column_width'  => 6,
                    'sort_order'    => 2,
                    'is_active'     => true,
                ]
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}