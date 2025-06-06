<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkingHoursRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'day_off' => ['required', 'string', 'in:Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday'],
        ];
    }
    public function messages(): array
    {
        return [
            'day_off.in' => 'اليوم غير صالح. يجب أن يكون أحد أيام الأسبوع بالإنجليزية.',
        ];
    }
}
