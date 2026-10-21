<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingClassRate extends Model
{
    protected $guarded = [];

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    public function shippingClass()
    {
        return $this->belongsTo(ShippingClass::class, 'shipping_class_id');
    }
}