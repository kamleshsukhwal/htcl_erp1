<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTeamMember extends Model
{
    protected $fillable = [

        'project_id',

        'member_name',

        'mobile',

        'email',

        'designation',

        'employee_code',

        'can_login',

        'email_notification',

        'sms_notification'
    ];

    protected $casts = [

        'can_login' => 'boolean',

        'email_notification' => 'boolean',

        'sms_notification' => 'boolean',
    ];

    // PROJECT RELATION
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // ATTENDANCE RELATION
    public function attendanceDays()
    {
        return $this->hasMany(
            AttendanceDay::class,
            'project_team_member_id'
        );
    }
}