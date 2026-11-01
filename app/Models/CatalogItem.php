<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogItem extends Model
{
    protected $guarded = ['id', 'factory_id', 'status', 'review_note'];

    protected $casts = ['price' => 'decimal:2'];

    public function factory()
    {
        return $this->belongsTo(FactoryAccount::class, 'factory_id');
    }

    public function category()
    {
        return $this->belongsTo(CatalogCategory::class, 'category_id');
    }

    public function photos()
    {
        return $this->hasMany(CatalogPhoto::class, 'item_id')->orderBy('id');
    }

    public function reviewVersion(): string
    {
        return hash('sha256', json_encode([$this->only(['factory_id', 'category_id', 'kind', 'title', 'description', 'price', 'currency', 'minimum_order', 'status']), $this->photos()->orderBy('id')->pluck('path')->all()], JSON_UNESCAPED_UNICODE));
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'approved')->whereHas('factory', fn ($q) => $q->where('status', 'approved'))
            ->whereHas('category', fn ($q) => $q->available());
    }
}
