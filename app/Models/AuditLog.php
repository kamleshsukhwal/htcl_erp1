<?php

 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
 
    protected $fillable = [
        'module_name',
        'record_id',
        'action',
        'old_data',
        'new_data',
        'performed_by'
    ];


    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];
}