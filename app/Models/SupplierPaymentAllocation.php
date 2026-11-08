<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPaymentAllocation extends Model
{
    use HasFactory;

    protected $table = 'supplier_payment_allocations';

    protected $fillable = [
        'supplier_payment_id',
        'purchase_id',
        'allocated_amount',
        'amount',
    ];

    protected $casts = [
        'allocated_amount' => 'float',
        'amount'           => 'float',
    ];

    public function payment()
    {
        return $this->belongsTo(SupplierPayment::class, 'supplier_payment_id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }
}