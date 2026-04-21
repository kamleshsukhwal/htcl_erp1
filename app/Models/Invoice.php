<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_no',
        'customer_id',
        'dc_out_id',
        'invoice_date',
        'subtotal',
        'tax',
        'total',
        'paid_amount',
        'status'
    ];

    // 🔹 Relationships

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
/*
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }*/

    public function payments()
{
    return $this->hasMany(\App\Models\Payment::class);
}
    // 🔹 Auto Invoice Number

   protected static function boot()
{
    parent::boot();

    static::creating(function ($invoice) {

        $last = Invoice::latest()->first();

        $number = $last ? (int) substr($last->invoice_no, -5) + 1 : 1;

        $invoice->invoice_no = 'INV-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    });
}
}