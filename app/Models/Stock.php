<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'boq_item_id',
        'item_name',
        'available_qty'
    ];
    protected $casts = [
    'available_qty' => 'float',
];

    public function boqItem()
    {
        return $this->belongsTo(BoqItem::class);
    }
}
