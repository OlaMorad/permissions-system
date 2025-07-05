<?php

namespace App\Http\Requests;

use App\Enums\StatusInternalMail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceiptStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uuid' => 'required|string|exists:transactions,uuid',
            'status' => ['required','string',
                Rule::in([
                    StatusInternalMail::APPROVED->value,
                    StatusInternalMail::REJECTED->value,
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'uuid.required' => 'يجب تحديد المعاملة.',
            'uuid.exists' => 'المعاملة غير موجودة.',
            'status.required' => 'يجب تحديد الحالة.',
            'status.in' => 'الحالة يجب أن تكون "مرسلة" أو "مرفوضة".',
        ];
    }
}
