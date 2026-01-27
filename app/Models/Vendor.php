<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'vendors';

    // Add this line
    protected $fillable = [
        'name',
        'email',
        'address',
        'status' // if you have it in DB
    ];
}
