<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatchGenerateReportRequest extends FormRequest
{
    public const MAX_STUDENTS = 40;

    public function authorize(): bool
    {
        return $this->user('coordinator') !== null;
    }

    public function rules(): array
    {
        return [
            'student_ids' => 'required|array|min:1|max:'.self::MAX_STUDENTS,
            'student_ids.*' => ['integer', Rule::exists('students', 'id')->whereNull('deleted_at')],
            'month' => 'required|date_format:Y-m',
        ];
    }

    public function messages(): array
    {
        return [
            'student_ids.max' => 'You can include at most '.self::MAX_STUDENTS.' students per batch.',
        ];
    }
}
