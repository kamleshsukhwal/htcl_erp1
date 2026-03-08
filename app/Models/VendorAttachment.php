<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'file_name',
        'original_name',
        'file_path',
        'mime_type',
        'file_size'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}