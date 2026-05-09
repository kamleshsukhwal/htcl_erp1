<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceDay extends Model
{
    protected $fillable = [

        'project_id',

        'project_team_member_id',

        'attendance_date',

        'total_hours',

        'status'
    ];

    public function sessions()
    {
        return $this->hasMany(
            AttendanceSession::class
        );
    }

    public function member()
    {
        return $this->belongsTo(
            ProjectTeamMember::class,
            'project_team_member_id'
        );
    }
}