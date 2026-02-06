<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'vendor_id',
        'po_number',
        'project_id',
        'order_date',
        'total_amount',
        'status',
        //'boq_id',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
