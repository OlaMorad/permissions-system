<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSpecializationRequest extends FormRequest
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
        $id = $this->route('id');

        return [
            'name' => 'sometimes|string|max:255|unique:specializations,name,' . $id,
            'bachelors_degree' => 'sometimes|string|max:255',
            'experience_years' => 'sometimes|array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'اسم الاختصاص هذا موجود بالفعل',
            'name.string' => 'اسم الاختصاص يجب أن يكون نصاً',
            'bachelors_degree.string' => 'الدرجة الجامعية يجب أن تكون نصاً.',
            'experience_years.array' => 'عدد السنوات المطلوبة يجب أن يكون بصيغة مصفوفة.',
        ];
    }
}
