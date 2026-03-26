<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;
    protected $table='leave_type';
    protected $fillable=[
        'id',
        'name',
        'max_allowed_days',
        'accural_enabled',
        'accrual_rate',
        'credit_forward_enabled',
        'is_paid',
        'half_day_allowed'
    ];


}
