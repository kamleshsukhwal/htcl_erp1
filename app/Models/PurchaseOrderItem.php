<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    // If your table name is standard 'purchase_order_items', you can skip this
    // protected $table = 'purchase_order_items';

protected $fillable = [
    'purchase_order_id',
    'boq_item_id',
    'ordered_qty',
    'unit_price',
    'total',
    'item_name',
    'is_manual'
];

public function boqItem()
{
    return $this->belongsTo(BoqItem::class);
}

public function purchaseOrder()
{
    return $this->belongsTo(PurchaseOrder::class);
}
    

    // Optional: relationship to your Item table
    
}
