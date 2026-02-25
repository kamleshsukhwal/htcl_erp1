<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ncr extends Model
{
    protected $fillable = [
        'project_id',
        'boq_item_id',
        'issue_description',
        'reported_by',
        'assigned_to',
        'status',
        'corrective_action'
    ];
}