<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoqItem extends Model
{
  protected $fillable = [
    'boq_id',
    'sn',
    'item_name',
    'description',
    'unit',
    'quantity',
    'rate',
    'total_amount',
    'scope',
    'item_code',
    'approved_make',
    'offered_make',
    'hsn_code'
];

public function files()
{
    return $this->hasMany(BoqItemFile::class, 'boq_item_id');
}
}
