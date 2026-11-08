<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPayment extends Model
{
    use HasFactory;

    protected $table = 'supplier_payments';

    protected $fillable = [
        'payment_number',
        'voucher_number',
        'supplier_id',
        'branch_id',
        'bank_account_id',
        'payment_date',
        'payment_method',
        'payment_type',
        'amount',
        'allocated_amount',
        'unallocated_amount',
        'transaction_reference',
        'reference_no',
        'cheque_number',
        'cheque_no',
        'cheque_date',
        'attachment_file',
        'status',
        'notes',
        'cancelled_reason',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'payment_date'       => 'date',
        'cheque_date'        => 'date',
        'amount'             => 'float',
        'allocated_amount'   => 'float',
        'unallocated_amount' => 'float',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function allocations()
    {
        return $this->hasMany(SupplierPaymentAllocation::class, 'supplier_payment_id');
    }
}