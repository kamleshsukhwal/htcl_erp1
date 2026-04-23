<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DcInItem extends Model
{
    protected $fillable = [
        'dc_in_id',
        'boq_item_id',
        'supplied_qty',
         'item_name'
    ];

   

    // Relationship with BOQ Item
    public function boqItem()
    {
        return $this->belongsTo(BoqItem::class);
    }

    public function dcIn()
{
    return $this->belongsTo(DcIn::class, 'dc_in_id');
}
}
