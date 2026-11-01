<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountBatch extends Model
{
    protected $fillable = ['title', 'file_hash', 'created_by', 'source', 'source_filters'];
    protected $casts = ['source_filters' => 'array'];

    public function statements()
    {
        return $this->hasMany(MerchantStatement::class, 'batch_id');
    }
}
