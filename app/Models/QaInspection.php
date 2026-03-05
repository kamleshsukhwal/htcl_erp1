<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QaInspection extends Model
{
    protected $fillable = [
        'project_id',
        'checklist_id',
        'boq_item_id',
        'vendor_id',
        'inspection_date',
        'status',
        'location',
        'remarks',
        'inspected_by'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Project
    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class, 'project_id');
    }

    // Checklist
    public function checklist()
    {
        return $this->belongsTo(\App\Models\QaChecklist::class, 'checklist_id');
    }

    // BOQ Item
    public function boqItem()
    {
        return $this->belongsTo(\App\Models\BoqItem::class, 'boq_item_id');
    }

    // Vendor
    public function vendor()
    {
        return $this->belongsTo(\App\Models\Vendor::class, 'vendor_id');
    }

    // Inspector (User)
    public function inspector()
    {
        return $this->belongsTo(\App\Models\User::class, 'inspected_by');
    }

    // Inspection Items (if checklist items stored separately)
    public function items()
    {
        return $this->hasMany(\App\Models\QaInspectionItem::class, 'inspection_id');
    }
}