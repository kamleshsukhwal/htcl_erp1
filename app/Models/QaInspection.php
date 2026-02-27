<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class QaInspection extends Model
{
    protected $fillable = [
        'project_id',
        'boq_item_id',
        'inspection_date',
        'status',
        'remarks',
        'inspected_by'
    ];

    public function checklists()
    {
        return $this->hasMany(QaChecklist::class, 'inspection_id');
    }
}
