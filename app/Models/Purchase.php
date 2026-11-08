<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'purchases';

    protected $fillable = [
        'purchase_no',
        'purchase_number',
        'purchase_order_id',
        'purchase_receipt_id',
        'supplier_id',
        'warehouse_id',
        'branch_id',
        'bank_account_id',
        'supplier_invoice_no',
        'supplier_challan_no',
        'purchase_date',
        'due_date',
        'receiving_date',
        'purchase_type',
        'total_amount',
        'subtotal',
        'tax_amount',
        'discount',
        'discount_amount',
        'shipping_cost',
        'other_charges',
        'round_off',
        'grand_total',
        'paid_amount',
        'due_amount',
        'payment_status',
        'payment_method',
        'payment_terms',
        'purchase_status',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'purchase_date'  => 'date',
        'due_date'       => 'date',
        'receiving_date' => 'date',
        'total_amount'   => 'float',
        'grand_total'    => 'float',
        'paid_amount'    => 'float',
        'due_amount'     => 'float',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function allocations()
    {
        return $this->hasMany(SupplierPaymentAllocation::class, 'purchase_id');
    }
}