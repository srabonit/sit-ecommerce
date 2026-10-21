<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DigitalProductDeliveryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $orderItems;
    public $digitalDetails;

    public function __construct($order, $orderItems, $digitalDetails = [])
    {
        $this->order = $order;
        $this->orderItems = $orderItems;
        $this->digitalDetails = $digitalDetails;
    }

    public function build()
    {
        $senderEmail = config('mail.from.address', 'info.shopnowbd@gmail.com');
        $senderName  = config('mail.from.name', config('app.name', 'SHOP NOW BD ONLINE'));
        $invoiceNo   = $this->order->invoice_no ?? $this->order->id;

        return $this->from($senderEmail, $senderName)
                    ->subject("⚡ Digital Delivery Access & Keys - [#{$invoiceNo}]")
                    ->view('emails.digital_delivery');
    }
}