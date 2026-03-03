<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QaInspection extends Model
{
        protected $fillable = [
            'project_id',
            'checklist_id',
            'boq_item_id',
            'inspection_date',
            'status',
            'remarks',
            'inspected_by'
        ];


    
    
    public function checklist()
    {
        return $this->belongsTo(QaChecklist::class, 'checklist_id');
    }

    public function items()
    {
        return $this->hasMany(QaInspectionItem::class, 'inspection_id');
    }
}