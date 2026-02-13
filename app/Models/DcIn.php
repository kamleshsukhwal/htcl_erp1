<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DcIn extends Model
{
    protected $fillable = [
        'dc_number',
        'vendor_id',
        'purchase_order_id',
        'delivery_channel',
        'delivery_date'
    ];

    public function items()
    {
        return $this->hasMany(DcInItem::class);
    }
}
