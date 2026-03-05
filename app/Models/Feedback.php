<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feedback extends Model
{
    use SoftDeletes;

    protected $fillable = ['employee_id', 'feedback_text'];

    public function user()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
