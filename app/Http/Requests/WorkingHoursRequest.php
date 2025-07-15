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
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i'],
            'day_off' => ['sometimes', 'array'],
            'day_off.*' => ['string', 'in:السبت,الأحد,الاثنين,الثلاثاء,الأربعاء,الخميس,الجمعة'],
        ];
    }
    public function messages(): array
    {
        return [
            'start_time.date_format' => 'HH:MM تنسيق وقت بداية الدوام غير صحيح. يجب أن يكون على الشكل',
            'end_time.date_format' => 'HH:MM تنسيق وقت نهاية الدوام غير صحيح. يجب أن يكون على الشكل',
            'day_off.array' => 'يجب إدخال أيام العطلة في مصفوفة',
            'day_off.string' => 'يجب أن يكون يوم العطلة نصاً',
            'day_off.*.in' => 'أحد الأيام المدخلة غير صحيح. يرجى إدخال أيام الأسبوع باللغة العربية',
        ];
    }
}
