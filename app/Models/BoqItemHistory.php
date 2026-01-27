<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoqItemHistory extends Model
{
 
    protected $fillable = [
        'boq_item_id',
        'boq_id',
        'old_quantity',
        'new_quantity',
        'old_rate',
        'new_rate',
        'changed_by',
        'change_reason'
    ];
}
