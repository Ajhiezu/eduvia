<?php

namespace App\Http\Requests\Admission;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PublicApplicantRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Public registration form is accessible by anyone
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'admission_track_id' => ['required', 'exists:admission_tracks,id'],
            'target_grade_level_id' => ['nullable', 'exists:grade_levels,id'],
            'full_name' => ['required', 'string', 'max:150'],
            'nisn' => ['nullable', 'string', 'max:20'],
            'nik' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', 'in:male,female,L,P'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date'],
            'religion' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'string', 'email', 'max:150'],
            'previous_school' => ['required', 'string', 'max:150'],
            'parent_name' => ['required', 'string', 'max:150'],
            'parent_phone' => ['required', 'string', 'max:30'],
            'parent_relationship' => ['required', 'string', 'max:50'],

            // Documents (Max 3MB per doc, private storage, validated mime types)
            'document_kk' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:3072'],
            'document_akta' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:3072'],
            'document_ijazah' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:3072'],
            'document_rapor' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'document_sertifikat' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:3072'],
            'document_foto' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'admission_track_id' => 'Jalur Pendaftaran',
            'target_grade_level_id' => 'Tingkat Kelas',
            'full_name' => 'Nama Lengkap Calon Siswa',
            'nisn' => 'NISN',
            'nik' => 'NIK',
            'gender' => 'Jenis Kelamin',
            'birth_place' => 'Tempat Lahir',
            'birth_date' => 'Tanggal Lahir',
            'religion' => 'Agama',
            'address' => 'Alamat Tempat Tinggal',
            'phone' => 'Nomor HP / WhatsApp',
            'previous_school' => 'Asal Sekolah',
            'parent_name' => 'Nama Orang Tua / Wali',
            'parent_phone' => 'Nomor HP Orang Tua',
            'parent_relationship' => 'Hubungan Keluarga',
            'document_kk' => 'Kartu Keluarga',
            'document_akta' => 'Akta Kelahiran',
            'document_ijazah' => 'Ijazah / Surat Kelulusan',
            'document_rapor' => 'Buku Rapor',
            'document_sertifikat' => 'Sertifikat Prestasi',
            'document_foto' => 'Pas Foto',
        ];
    }
}
