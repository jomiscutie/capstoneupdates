<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimeOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('student') !== null || $this->routeIs('kiosk.*');
    }

    public function rules(): array
    {
        return [
            'face_encoding' => 'required_unless:verification_method,password|nullable|string',
            'recorded_at' => 'nullable|date',
            'verification_confidence' => 'nullable|numeric|min:0|max:100',
            'verification_method' => 'nullable|string|in:face,password',
            'password' => 'nullable|string',
            'verification_reason' => 'nullable|required_if:verification_method,password|string|max:255',
            'verification_snapshot' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ];
    }
}
