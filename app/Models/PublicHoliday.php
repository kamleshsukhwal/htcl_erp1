<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicHoliday extends Model
{
    protected $table = 'public_holiday';

    protected $fillable = [
        'id',
        'name',
        'date',
    ];
}
