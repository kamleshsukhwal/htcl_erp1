<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee_detail;
use App\Models\Employee_profile;
use App\Models\Employee_document;
use App\Models\Attendence;
use App\Models\LeaveApplication;
use App\Models\LetterFormat;
use App\Models\Employee_department;
class Employee extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasFactory;
    protected $table = 'employees';
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
        return $this->hasMany(Employee_document::class,'employee_id');
    }

    public function attendence(){
        return $this->hasMany(Attendence::class,'employee_id');
    }

    public function leaveApplication(){
        return $this->hasMany(LeaveApplication::class,'employee_id');
    }

    public function hrletterformat(){
        return $this->hasMany(LetterFormat::class,'employee_id');
    }

    public function employeeDepartments()
    {
        return $this->hasMany(Employee_department::class,'employee_id');
    }
}
