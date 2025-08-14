<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;

class ConvertStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->route('type'); // إما manager أو employee

        return [
            'user_id' => [
                'required',
                function ($attribute, $value, $fail) use ($type) {
                    if ($type === 'employee') {
                        $exists = DB::table('employees')->where('id', $value)->exists();
                    } elseif ($type === 'manager') {
                        $exists = DB::table('managers')->where('id', $value)->exists();
                    } else {
                        $fail('نوع المستخدم غير صحيح.');
                        return;
                    }

                    if (!$exists) {
                        $fail('المعرف غير موجود في جدول ' . $type);
                    }
                }
            ],
        ];
    }
}
