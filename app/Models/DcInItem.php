<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DcInItem extends Model
{
    protected $fillable = [
        'dc_in_id',
        'boq_item_id',
        'supplied_qty'
    ];

    // Relationship with DC In
    public function dcIn()
    {
        return $this->belongsTo(DcIn::class);
    }

    // Relationship with BOQ Item
    public function boqItem()
    {
        return $this->belongsTo(BoqItem::class);
    }
}
