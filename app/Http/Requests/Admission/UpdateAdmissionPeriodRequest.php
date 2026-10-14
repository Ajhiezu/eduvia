<?php

namespace App\Http\Requests\Admission;

use App\Enums\UserRole;
use App\Models\AdmissionPeriod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAdmissionPeriodRequest extends FormRequest
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
        /** @var AdmissionPeriod $period */
        $period = $this->route('period');

        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'announcement_date' => ['required', 'date', 'after_or_equal:end_date'],
            're_registration_start_date' => ['required', 'date', 'after_or_equal:announcement_date'],
            're_registration_end_date' => ['required', 'date', 'after_or_equal:re_registration_start_date'],
            'quota' => ['required', 'integer', 'min:1', 'max:10000'],
            'status' => ['required', 'in:draft,open,closed,completed'],
            'description' => ['nullable', 'string', 'max:1000'],
            'requirements' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
