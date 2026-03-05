<?php

namespace App\Models;
use App\Models\Project;
use App\Models\BoqItem;
use App\Models\UserController;
use Illuminate\Database\Eloquent\Model;

class Ncr extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'boq_item_id',
        'issue_description',
        'reported_by',
        'assigned_to',
        'severity',
        'status',
        'due_date',
        'corrective_action'
    ];
     public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function boqItem()
    {
        return $this->belongsTo(BoqItem::class, 'boq_item_id');
    }
    public function assignedUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }
}