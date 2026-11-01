<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogPhoto extends Model
{
    protected $guarded = ['id'];

    public function item()
    {
        return $this->belongsTo(CatalogItem::class, 'item_id');
    }
}
