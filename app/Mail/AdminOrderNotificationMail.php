<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminOrderNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $orderItems;

    public function __construct($order, $orderItems)
    {
        $this->order = $order;
        $this->orderItems = $orderItems;
    }

    public function build()
    {
        $senderEmail = config('mail.from.address', 'info.srabonit@gmail.com');
        $senderName  = config('mail.from.name', config('app.name', 'SIT ECOMMERCE'));

        return $this->from($senderEmail, $senderName)
                    ->subject('🔔 New Order Received! Invoice #' . $this->order->invoice_no)
                    ->view('emails.admin_order_notification');
    }
}