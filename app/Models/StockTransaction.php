<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
    'boq_item_id',
    'item_name',
    'type',
    'quantity',
    'reference_type',
    'reference_id'
];

public function boqItem()
{
    return $this->belongsTo(BoqItem::class, 'boq_item_id');
}
}