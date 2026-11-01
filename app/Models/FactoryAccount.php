<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class FactoryAccount extends Authenticatable
{
    protected $guarded = ['id', 'status', 'review_note'];

    protected $attributes = ['status' => 'pending'];

    protected $hidden = ['password', 'remember_token', 'email'];

    public function items()
    {
        return $this->hasMany(CatalogItem::class, 'factory_id');
    }

    public function reviewVersion(): string
    {
        return hash('sha256', json_encode($this->only(['name', 'email', 'phone', 'city', 'activity', 'description', 'status']), JSON_UNESCAPED_UNICODE));
    }
}
