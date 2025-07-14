<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddSpecializationRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:specializations,name',
            'bachelors_degree' => 'required|string|max:255',
            'experience_years' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'يرجى ادخال اسم الاختصاص',
            'name.unique' => 'لا يمكن إدخال اختصاصين بنفس الاسم.',
            'bachelors_degree.required' => 'يرجى ادخال الدرجة الجامعية',
            'experience_years.required' => 'يرجة ادخال عدد السنوات المطلوبة',
            'experience_years.array' => 'عدد السنوات المطلوبة يجب أن يكون بضيغة مصفوفة',
        ];
    }
}
