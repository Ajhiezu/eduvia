<?php

namespace App\Models;

use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolRoom extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'type',
        'capacity',
        'location',
        'manager_name',
        'facilities_description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(FacilityReservation::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(FacilityIncident::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'laboratory' => 'Laboratorium',
            'classroom' => 'Ruang Kelas',
            'hall' => 'Aula / Gedung Pertemuan',
            'sports_field' => 'Fasilitas Olahraga',
            'meeting_room' => 'Ruang Rapat',
            'workshop' => 'Bengkel / Workshop Praktik',
            'library_room' => 'Ruang Baca Perpustakaan',
            default => ucfirst($this->type),
        };
    }
}
