<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\Department;
class Employee_department extends Model
{
    protected $table='employee_department';
    protected $fillable = ['employee_id', 'department_id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class,'department_id');
    }
}
