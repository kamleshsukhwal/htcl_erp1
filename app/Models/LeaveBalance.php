<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\LeaveType;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $table='leave_balance';
    protected $fillable = [
        'id',
        'employee_id',
        'leave_type_id',
        'max_allowed',
        'used_leave'
    ];

    public function employee(){
        return $this->belongsTo(Employee::class,'employee_id');
    }

    public function leavetype(){
        return $this->belongsTo(LeaveType::class,'leave_type_id');
    }
}
