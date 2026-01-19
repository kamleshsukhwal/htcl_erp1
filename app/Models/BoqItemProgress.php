<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoqItemProgress extends Model
 {
    protected $fillable = [
        'boq_item_id',
        'executed_qty',
        'entry_date',
        'remarks'
    ];


}
