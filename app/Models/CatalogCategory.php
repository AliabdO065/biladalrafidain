<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogCategory extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['active' => 'boolean'];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function getLabelAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function scopeAvailable($query)
    {
        return $query->where('active', true)->where(fn ($q) => $q->whereNull('parent_id')->orWhereHas('parent', fn ($p) => $p->where('active', true)));
    }
}
