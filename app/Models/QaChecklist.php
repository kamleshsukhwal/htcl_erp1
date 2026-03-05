<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QaChecklist extends Model
{
    protected $fillable = [
       // 'inspection_id',
       // 'check_point',
       'name',
        'expected_value',
        'actual_value',
        'status'
    ];

   public function items()
    {
        return $this->hasMany(QaChecklistItem::class, 'checklist_id');
    }
    
    }