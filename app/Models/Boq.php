<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Project;
use App\Models\BoqItem;
use App\Models\BoqFile;

class Boq extends Model
{
    protected $fillable = [
        'project_id',
        'boq_name',
        'discipline',
        'status',
        'total_amount',
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

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
