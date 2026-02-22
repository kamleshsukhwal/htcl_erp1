<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
   use HasFactory;

    protected $fillable = [
        'client_code',
        'name',
        'email',
        'phone',
        'pancard_no',
        'gst_no',
        'status'
    ];
}
