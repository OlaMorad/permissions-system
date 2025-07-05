<?php

namespace App\Http\Requests;

use App\Enums\StatusInternalMail;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;

class StatusRequest extends FormRequest
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
            'uuid'=>'required|exists:internal_mails,uuid',
            'status'=>['required',new Enum(StatusInternalMail::class)]
        ];
    }
}
