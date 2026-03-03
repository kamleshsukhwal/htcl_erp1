<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QaChecklistItem extends Model
{
    protected $table = 'qa_checklist_items';

    protected $fillable = [
        'checklist_id',
        'check_point',
        'type',
        'is_required',
    ];

    public function checklist()
    {
        return $this->belongsTo(QaChecklist::class, 'checklist_id');
    }
    
}