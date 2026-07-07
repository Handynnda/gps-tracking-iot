<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'kode_kendaraan',
        'nama_kendaraan',
        'plat_nomor',
        'device_id',
    ];
}