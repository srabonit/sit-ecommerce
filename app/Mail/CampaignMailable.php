<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;

class CampaignMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $campaign;
    public $recipient;
    public $subscriber;

    /**
     * Create a new message instance.
     */
    public function __construct(EmailCampaign $campaign, EmailCampaignRecipient $recipient, ?EmailSubscriber $subscriber = null)
    {
        $this->campaign   = $campaign;
        $this->recipient  = $recipient;
        $this->subscriber = $subscriber;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $fromName  = $this->campaign->sender_name ?: config('mail.from.name', 'SHOP NOW BD');
        $fromEmail = $this->campaign->sender_email ?: config('mail.from.address', 'newsletter@shopnowbd.online');

        // Render HTML content
        $html = $this->campaign->template ? $this->campaign->template->html_content : '<p>Empty Campaign Content</p>';

        // Determine First & Full Name
        $firstName    = $this->subscriber->first_name ?? 'Valued';
        $customerName = trim(($this->subscriber->first_name ?? '') . ' ' . ($this->subscriber->last_name ?? '')) ?: 'Customer';

        // URLs
        $shopUrl        = url('/');
        $trackingToken  = $this->recipient->tracking_token;
        $openPixelUrl   = url("email/track/open/{$trackingToken}");
        $unsubUrl       = $this->subscriber ? url("email/unsubscribe/{$this->subscriber->unsubscribe_token}") : url("email/unsubscribe/{$trackingToken}");

        // Coupon code
        $couponCode = $this->campaign->coupon ? $this->campaign->coupon->code : '';

        // Dynamic Merge Replacement
        $html = str_replace([
            '{customer_name}',
            '{first_name}',
            '{email}',
            '{shop_url}',
            '{unsubscribe_url}',
            '{coupon_code}'
        ], [
            e($customerName),
            e($firstName),
            e($this->recipient->email),
            $shopUrl,
            $unsubUrl,
            $couponCode
        ], $html);

        // Inject Click-Tracking Wrapper to anchor tags
        $html = preg_replace_callback('/<a\s+([^>]*?)href=["\']([^"\']*)["\']([^>]*?)>/i', function ($matches) use ($trackingToken) {
            $prefix    = $matches[1];
            $original  = $matches[2];
            $suffix    = $matches[3];

            if (str_starts_with($original, '#') || str_starts_with($original, 'mailto:') || str_starts_with($original, 'tel:') || str_contains($original, 'email/track') || str_contains($original, 'email/unsubscribe')) {
                return $matches[0];
            }

            $redirectUrl = url("email/track/click/{$trackingToken}?target=" . urlencode($original));
            return "<a {$prefix}href=\"{$redirectUrl}\"{$suffix}>";
        }, $html);

        // Inject 1x1 Transparent Open Tracking Pixel before </body>
        $pixelTag = "<img src=\"{$openPixelUrl}\" width=\"1\" height=\"1\" alt=\"\" style=\"display:none;width:1px;height:1px;\" border=\"0\" />";
        if (str_contains($html, '</body>')) {
            $html = str_replace('</body>', $pixelTag . '</body>', $html);
        } else {
            $html .= $pixelTag;
        }

        return $this->from($fromEmail, $fromName)
                    ->subject($this->campaign->subject)
                    ->html($html);
    }
}