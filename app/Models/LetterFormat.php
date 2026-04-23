<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterFormat extends Model
{
    protected $table = 'letter_formats';
    protected $fillable = [
        'id',
        'message_type',
        'message',
        'added_time',
        'updated_on',
        'added_by',
        'updated_by'
    ];
}
