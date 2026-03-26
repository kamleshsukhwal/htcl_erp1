<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Employee;
use function Laravel\Prompts\table;

class Attendence extends Model
{
    use HasFactory;
    protected $table = 'attendence';
    protected $fillable = [
        'employee_id',
        'Attendence_date',
        'In_time',
        'Out_time'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }
}
