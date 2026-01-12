<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

 
    class Boq extends Model
{
    protected $fillable = [
        'project_id',
        'boq_name',
        'discipline',
        'status',
        'created_by'
    ];

    public function items()
    {
        return $this->hasMany(BoqItem::class);
    }

    public function files()
    {
        return $this->hasMany(BoqFile::class);
    }
}

