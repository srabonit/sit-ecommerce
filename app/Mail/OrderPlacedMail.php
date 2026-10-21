<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
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
        $senderEmail = config('mail.from.address', 'info.srabonit@gmail.com');
        $senderName  = config('mail.from.name', config('app.name', 'SIT ECOMMERCE'));
        $invoiceNo   = $this->order->invoice_no ?? $this->order->id;

        return $this->from($senderEmail, $senderName)
                    ->subject('Order Confirmation - Invoice #' . $invoiceNo)
                    ->view('emails.order_placed');
    }
}