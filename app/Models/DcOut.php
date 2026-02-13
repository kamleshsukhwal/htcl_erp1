<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

 
   class DcOut extends Model
{
    protected $fillable = [
        'dc_number',
        'project_id',
        'issue_date',
        'issued_to'
    ];

    public function items()
    {
        return $this->hasMany(DcOutItem::class);
    }
}