<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolAsset extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = [
        'school_id',
        'inventory_category_id',
        'asset_code',
        'name',
        'brand',
        'model_number',
        'serial_number',
        'room_location',
        'condition',
        'status',
        'acquisition_date',
        'purchase_cost',
        'responsible_person',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'purchase_cost' => 'integer',
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
        return $this->hasMany(AssetLog::class)->orderBy('created_at', 'desc');
    }

    public function conditionLabel(): string
    {
        return match ($this->condition) {
            'good' => 'Baik',
            'minor_damage' => 'Rusak Ringan',
            'heavy_damage' => 'Rusak Berat',
            'disposed' => 'Dihapuskan',
            default => ucfirst($this->condition),
        };
    }

    public function conditionVariant(): string
    {
        return match ($this->condition) {
            'good' => 'success',
            'minor_damage' => 'warning',
            'heavy_damage' => 'danger',
            'disposed' => 'neutral',
            default => 'neutral',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'in_use' => 'Digunakan',
            'in_storage' => 'Di Gudang',
            'maintenance' => 'Dalam Perbaikan',
            'disposed' => 'Afkir/Dihapuskan',
            default => ucfirst($this->status),
        };
    }

    public function formattedCost(): string
    {
        return 'Rp '.number_format($this->purchase_cost, 0, ',', '.');
    }
}
