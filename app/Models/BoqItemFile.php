<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoqItemFile extends Model
{
    protected $fillable = [
        'boq_item_id',
        'file_name',
        'file_path',
        'file_type',
        'uploaded_by'
    ];
}