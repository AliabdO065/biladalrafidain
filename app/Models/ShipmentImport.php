<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentImport extends Model
{
    protected $fillable = ['file_name', 'file_hash', 'created_by', 'shipments_created', 'shipments_skipped', 'trips_created'];
}
