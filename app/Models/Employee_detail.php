<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
class Employee_detail extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeDetailFactory> */
    use HasFactory;
    protected $table = 'employee_details';

    protected $fillable = [
        'employee_id',
        'DOB',
        'photo',
        'address',
        'bank_account_number',
        'bank_name',
        'ifsc_code',
        'contact_number',
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }
}
