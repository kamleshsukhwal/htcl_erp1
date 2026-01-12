<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_code',
        'project_name',
        'client_name',
        'client_email',
        'client_phone',
        'project_type',
        'start_date',
        'end_date',
        'project_value',
        'status',
        'description',
        'created_by'
    ];
}
