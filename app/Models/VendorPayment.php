<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPayment extends Model
{
    //

    protected $fillable = [
    'po_id',
    'vendor_id',
    'amount',
    'payment_date',
    'mode',
    'txn_ref_no',
    'attachment',
    'remarks'
];

public function payments()
{
    return $this->hasMany(VendorPayment::class, 'po_id');
}
}
