<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoqItem extends Model
{
    protected $fillable = [
        'boq_id',
        'sn',
        'description',
        'unit',
        'quantity',
        'rate',
        'total_amount',
        'scope',
        'approved_make',
        'offered_make'
    ];
}
