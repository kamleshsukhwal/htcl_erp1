<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee_detail;
use App\Models\Employee_profile;
use App\Models\employee_document;
class Employee extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'gender',
        'email_id',
        'role'
    ];

    public function details()
    {
        return $this->hasOne(Employee_detail::class,'employee_id');
    }

    public function profile()
    {
        return $this->hasOne(Employee_profile::class,'employee_id');
    }

    public function documents()
    {
        return $this->hasMany(employee_document::class,'employee_id');
    }
}
