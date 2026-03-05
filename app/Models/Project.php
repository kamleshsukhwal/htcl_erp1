<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'project_code',
        'project_name',
        'client_id',
        'client_name',
        'client_email',
        'client_phone',
        'project_type',
        'start_date',
        'end_date',
        'project_value',
        'approved_budget',
        'actual_cost',
        'billing_type',
        'progress_percent',
        'status',
        'remarks',
        'project_manager_id',
        'assigned_users',
        'created_by'
    ];

    protected $casts = [
        'assigned_users' => 'array',
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    // 🔗 Project → BOQs
    public function boqs()
    {
        return $this->hasMany(Boq::class);
    }

    // 🔗 Project Manager
    public function manager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }
    
     public function ncrs()
    {
        return $this->hasMany(Ncr::class);
    }
}