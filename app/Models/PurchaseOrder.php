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

        // ✅ NEW FIELDS
        'delivery_date',
        'approved_by',
        'approved_status',

        'total_amount',
        'gst_amount',
        'deliver_to',
        'status',
        't_c',
        'notes',
];

  public function items()
{
    return $this->hasMany(\App\Models\PurchaseOrderItem::class, 'purchase_order_id');
}
    public function invoice()
{
    return $this->hasOne(\App\Models\Invoice::class, 'po_id', 'id');
}

   // 🔗 Optional: who approved
    public function approvedUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}