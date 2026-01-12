<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoqFile extends Model
{
    protected $fillable = [
        'boq_id',
        'file_name',
        'file_path',
        'file_type',
        'uploaded_by'
    ];
}
