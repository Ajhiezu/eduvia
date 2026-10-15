<?php

namespace App\Http\Requests\Subject;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:30'],
            'group' => ['required', 'string', 'in:Umum,Peminatan,Kejuruan,Muatan Lokal'],
            'curriculum' => ['nullable', 'string', 'in:merdeka,k13,custom'],
            'educational_level' => ['nullable', 'string', 'in:sd,smp,sma,smk'],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:20'],
            'academic_program' => ['nullable', 'string', 'max:100'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama Mata Pelajaran',
            'code' => 'Kode Mapel',
            'group' => 'Kelompok Mapel',
            'curriculum' => 'Kurikulum',
            'educational_level' => 'Jenjang Sekolah',
            'credit_hours' => 'Beban Jam Pelajaran (JP/SKS)',
            'academic_program' => 'Peminatan / Kompetensi Keahlian',
            'order' => 'Nomor Urut',
            'is_active' => 'Status Aktif',
            'description' => 'Deskripsi / Capaian',
        ];
    }
}
