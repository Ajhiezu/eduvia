<?php

namespace App\Http\Requests\School;

use App\Enums\UserRole;
use App\Models\School;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchoolProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $school = $this->route('school') ?? School::current();
        $schoolId = $school?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:100'],
            'npsn' => ['nullable', 'string', 'regex:/^[0-9]{8}$/', Rule::unique('schools', 'npsn')->ignore($schoolId)],
            'educational_levels' => ['required', 'array', 'min:1'],
            'educational_levels.*' => ['string', Rule::in(['sd', 'smp', 'sma', 'smk'])],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
        ];
    }

    /**
     * Get custom Indonesian error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama resmi sekolah wajib diisi.',
            'npsn.regex' => 'NPSN harus berupa 8 digit angka resmi.',
            'npsn.unique' => 'NPSN ini sudah digunakan oleh sekolah lain.',
            'educational_levels.required' => 'Pilih setidaknya satu jenjang pendidikan sekolah.',
            'educational_levels.min' => 'Pilih setidaknya satu jenjang pendidikan sekolah.',
            'email.email' => 'Format email resmi sekolah tidak valid.',
            'website.url' => 'Format alamat website harus berupa URL valid (contoh: https://sekolah.sch.id).',
            'logo.image' => 'Berkas logo harus berupa gambar valid.',
            'logo.mimes' => 'Logo harus berformat PNG, JPG, JPEG, WEBP, atau SVG.',
            'logo.max' => 'Ukuran berkas logo maksimal 2 MB.',
        ];
    }
}
