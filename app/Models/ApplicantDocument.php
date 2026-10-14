<?php

namespace App\Models;

use Database\Factories\ApplicantDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'admission_applicant_id',
    'document_type',
    'file_path',
    'original_name',
    'file_size',
    'mime_type',
    'status',
    'review_notes',
    'verified_by',
    'verified_at',
])]
class ApplicantDocument extends Model
{
    /** @use HasFactory<ApplicantDocumentFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Applicant reference.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(AdmissionApplicant::class, 'admission_applicant_id');
    }

    /**
     * Verifier user reference.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Indonesian label for document type.
     */
    public function documentTypeLabel(): string
    {
        return match ($this->document_type) {
            'kartu_keluarga' => 'Kartu Keluarga (KK)',
            'akta_kelahiran' => 'Akta Kelahiran',
            'ijazah_skhun' => 'Ijazah / SKHUN / Surat Kelulusan',
            'rapor' => 'Buku Rapor Terakhir',
            'sertifikat' => 'Sertifikat Prestasi / Piagam',
            'foto' => 'Pas Foto Calon Siswa',
            'surat_pindah' => 'Surat Keterangan Pindah Tugas / Afirmasi',
            default => ucwords(str_replace('_', ' ', $this->document_type)),
        };
    }

    /**
     * Indonesian status label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Verifikasi',
            'valid' => 'Berkas Valid',
            'invalid' => 'Berkas Ditolak / Tidak Sesuai',
            default => ucfirst($this->status),
        };
    }

    /**
     * Badge visual color.
     */
    public function statusBadgeVariant(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'valid' => 'success',
            'invalid' => 'danger',
            default => 'neutral',
        };
    }
}
