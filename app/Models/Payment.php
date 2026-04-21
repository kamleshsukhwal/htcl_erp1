<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
   protected $fillable = [
    'invoice_id',
    'amount',
    'payment_date',
    'mode',
    'txn_ref_no',
    'attachment'
];

    // 🔹 Relationship

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function attachments()
{
    return $this->hasMany(Payment::class);
}
}