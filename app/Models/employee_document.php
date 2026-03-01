<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
class employee_document extends Model
{
    protected $fillable = [
        'employee_id',
        'document_name',
        'document_path',
        'document_type',
        'uploaded_by',
    ];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }

}
