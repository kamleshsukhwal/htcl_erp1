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
    'gst_amount',
    'status',
    't_c',
    'notes'
];

  public function items()
{
    return $this->hasMany(\App\Models\PurchaseOrderItem::class, 'purchase_order_id');
}
    public function invoice()
{
    return $this->hasOne(\App\Models\Invoice::class, 'po_id', 'id');
}
}

