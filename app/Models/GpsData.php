<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GpsData extends Model
{
    use HasFactory;

    // Ensure the columns you're trying to insert are in the $fillable array
    protected $fillable = [
        'imei',
        'gpsdata',
        'ioelements',
        'recorded_at',
    ];

    // Optionally, if you're using timestamps and don't want to include them in your insert,
    // you can disable them
    public $timestamps = true;
}
