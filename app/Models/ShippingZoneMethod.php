<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingZoneMethod extends Model
{
    protected $guarded = [];

    public function zone()
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }
}