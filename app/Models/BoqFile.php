<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class BoqFile extends Model
{

    use softDeletes;
   
    protected $fillable = [
        'boq_id',
        'file_name',
        'file_path',
        'file_type',
        'uploaded_by'
    ];
}
