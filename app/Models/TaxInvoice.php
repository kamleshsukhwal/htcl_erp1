<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxInvoice extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'invoice_no',
        'invoice_date',
        'invoice_amount',
        'gst_amount',
        'tds_amount',
        'payable_amount',
        'remarks',
        'invoice_file',
        'status',
        'uploaded_by'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}