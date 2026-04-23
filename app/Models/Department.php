<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee_profile;
use App\Models\Employee_department;
class Department extends Model
{
    protected $table = 'department';
    protected $fillable = ['department_name'];

    public function employeeDepartments()
    {
        return $this->hasMany(Employee_department::class,'department_id');
    }
}
