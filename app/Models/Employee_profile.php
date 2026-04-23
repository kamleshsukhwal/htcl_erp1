<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Employee;
use App\Models\Department;
class Employee_profile extends Model
{
    use HasFactory;
    protected $fillable=[
        'employee_id',
        'department_id',
        'aadhar_number',
        'pan_number',
        'employement_type',
        'degree_name',
        'college_name',
        'year_of_passing',
        'experience'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }

    public function department(){
        return $this->belongsTo(Department::class,'department_id');
    }
}
