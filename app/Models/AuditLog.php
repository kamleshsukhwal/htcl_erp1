<?php

 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'module',
        'module_id',
        'action',
        'old_data',
        'new_data',
        'user_id'
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];
}