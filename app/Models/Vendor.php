<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'vendors';

    // Add this line
    protected $fillable = [
        'name',
        'email',
        'address',
        'status', // if you have it in DB
        'mobile',
        'gst_number',
        'vendor_code',
        'pancard'
     
    ];

    public function attachments()
{
    return $this->hasMany(VendorAttachment::class);
}

public function payments()
{
    return $this->hasMany(VendorPayment::class, 'vendor_id');
}
}
