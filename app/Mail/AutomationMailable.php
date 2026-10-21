<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailAutomation;
use App\Models\EmailAutomationStep;
use App\Models\EmailAbandonedCart;
use App\Models\Order;

class AutomationMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $automation;
    public $step;
    public $email;
    public $cart;
    public $order;
    public $customData;

    /**
     * Create a new message instance.
     */
    public function __construct(
        EmailAutomation $automation,
        EmailAutomationStep $step,
        string $email,
        ?EmailAbandonedCart $cart = null,
        ?Order $order = null,
        array $customData = []
    ) {
        $this->automation = $automation;
        $this->step       = $step;
        $this->email      = $email;
        $this->cart       = $cart;
        $this->order      = $order;
        $this->customData = $customData;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->step->subject
            ?: ($this->step->template->subject ?? "Special Update from " . config('mail.from.name', 'SHOP NOW BD'));

        $html = $this->step->template ? $this->step->template->html_content : '<p>Automated Lifecycle Notification</p>';

        $shopUrl = url('/');
        $firstName = $this->customData['first_name'] ?? 'Valued Customer';
        $cartRecoveryUrl = $this->cart ? url("email/cart-recovery/{$this->cart->recovery_token}") : $shopUrl;
        $couponCode = $this->customData['coupon_code'] ?? 'WELCOME10';

        // Cart items table builder if abandoned cart
        $cartItemsHtml = '';
        if ($this->cart && !empty($this->cart->cart_items)) {
            $items = is_array($this->cart->cart_items) ? $this->cart->cart_items : json_decode($this->cart->cart_items, true);
            if (is_array($items) && count($items) > 0) {
                $cartItemsHtml = '<table width="100%" cellpadding="6" cellspacing="0" style="border: 1px solid #e2e8f0; font-family: sans-serif; font-size: 13px; margin: 15px 0;">';
                $cartItemsHtml .= '<tr style="background:#f8fafc; text-align:left;"><th>Product</th><th>Qty</th><th>Price</th></tr>';
                foreach ($items as $item) {
                    $cartItemsHtml .= '<tr style="border-bottom: 1px solid #eee;">';
                    $cartItemsHtml .= '<td>' . e($item['name'] ?? 'Product') . '</td>';
                    $cartItemsHtml .= '<td>' . ($item['quantity'] ?? 1) . '</td>';
                    $cartItemsHtml .= '<td>৳ ' . number_format($item['price'] ?? 0, 2) . '</td>';
                    $cartItemsHtml .= '</tr>';
                }
                $cartItemsHtml .= '</table>';
            }
        }

        // Replace Merge Tags
        $html = str_replace([
            '{customer_name}',
            '{first_name}',
            '{email}',
            '{shop_url}',
            '{cart_recovery_url}',
            '{cart_items}',
            '{coupon_code}',
            '{unsubscribe_url}'
        ], [
            e($firstName),
            e($firstName),
            e($this->email),
            $shopUrl,
            $cartRecoveryUrl,
            $cartItemsHtml,
            $couponCode,
            url('email/unsubscribe/' . md5($this->email))
        ], $html);

        return $this->subject($subject)
                    ->from(config('mail.from.address', 'newsletter@shopnowbd.online'), config('mail.from.name', 'SHOP NOW BD'))
                    ->html($html);
    }
}