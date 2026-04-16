<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Invoice;
class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'item_id',
        'item_name',
        'qty',
        'price',
        'amount'
    ];

    // 🔹 Relationship

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}