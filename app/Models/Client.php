<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
   use HasFactory;
/*SELECT `id`, `client_code`, `name`, `email`, `phone`, `company_name`, 
`contact_person`, `alternate_phone`, `address`, `gst_no`, `status`, `created_at`,
 `updated_at`, `deleted_at`, `pancard_no` FROM `clients` WHERE 1*/
    protected $fillable = [
        'client_code',
        'name',
        'email',
        'phone',
       'in_site_location',
        'contact_person',
        'alternate_phone',
        'address',
        'pancard_no',
        'gst_no',
        'status'
    ];
}
