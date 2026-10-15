<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_asset_id',
        'action',
        'from_location',
        'to_location',
        'from_condition',
        'to_condition',
        'notes',
        'performed_by',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(SchoolAsset::class, 'school_asset_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
