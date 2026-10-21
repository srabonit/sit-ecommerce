<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'credentials',
        'is_live',
        'is_active',
    ];

    protected $casts = [
        'credentials' => 'array', // Encapsulates dynamic JSON inputs
        'is_live'     => 'boolean',
        'is_active'   => 'boolean',
    ];
}