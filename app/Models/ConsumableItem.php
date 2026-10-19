<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsumableItem extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = [
        'school_id',
        'inventory_category_id',
        'item_code',
        'name',
        'unit',
        'current_stock',
        'minimum_stock',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'integer',
            'minimum_stock' => 'integer',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ConsumableStockLog::class)->orderBy('created_at', 'desc');
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }
}
