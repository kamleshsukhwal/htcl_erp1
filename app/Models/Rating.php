<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rating extends Model
{
    use SoftDeletes;

    protected $fillable = ['employee_id', 'rating'];

    public function user()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}