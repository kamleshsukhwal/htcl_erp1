<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    // If your table name is standard 'purchase_order_items', you can skip this
    // protected $table = 'purchase_order_items';

    // Allow mass assignment
  protected $fillable = ['purchase_order_id',  'item_name', 'ordered_qty', 'boq_id','unit_price', 'total'];

public function purchaseOrder()
{
    return $this->belongsTo(PurchaseOrder::class);
}
    

    // Optional: relationship to your Item table
    
}
