<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Employee;
class Employee_profile extends Model
{
    use HasFactory;
    protected $fillable=[
        'employee_id',
        'Aadhar_Number',
        'PAN_Number',
        'Employement_Type',
        'Degree_Name',
        'College_Name',
        'Year_of_passing',
        'Experience'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }
}
