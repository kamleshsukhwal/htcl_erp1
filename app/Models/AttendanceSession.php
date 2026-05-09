<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    protected $fillable = [

        'attendance_day_id',

        'check_in',
        'check_out',

        'checkin_latitude',
        'checkin_longitude',

        'checkout_latitude',
        'checkout_longitude',

        'checkin_image',
        'checkout_image',

        'worked_hours',

        'remarks'
    ];

    protected $casts = [

        'check_in' => 'datetime',

        'check_out' => 'datetime',

        'worked_hours' => 'decimal:2',
    ];

    // ATTENDANCE DAY
    public function attendanceDay()
    {
        return $this->belongsTo(
            AttendanceDay::class
        );
    }
}