<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

   class DcOutItem extends Model
{
    protected $fillable = [
        'dc_out_id',
        'boq_item_id',
        'issued_qty'
    ];

   
    public function boqItem()
{
    return $this->belongsTo(BoqItem::class, 'boq_item_id');
}
}