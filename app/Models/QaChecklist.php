<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QaChecklist extends Model
{
    protected $fillable = [
        'inspection_id',
        'check_point',
        'expected_value',
        'actual_value',
        'status'
    ];
}
