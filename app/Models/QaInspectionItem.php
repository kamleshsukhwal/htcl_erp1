<?php

 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QaInspectionItem extends Model
{
    protected $table = 'qa_inspection_items';

    protected $fillable = [
        'inspection_id',
        'checklist_item_id',
        'result',
        'remarks'
    ];

    public function inspection()
    {
        return $this->belongsTo(QaInspection::class);
    }

    public function checklistItem()
    {
        return $this->belongsTo(QaChecklistItem::class, 'checklist_item_id');
    }
}