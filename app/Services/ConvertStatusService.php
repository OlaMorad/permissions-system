<?php

namespace App\Services;

use App\Http\Resources\successResource;
use App\Models\Employee;
use App\Models\Manager;

class ConvertStatusService
{
    public function Convert_status_for_user($id, $type)
    {
        $model = match ($type) {
            'manager'  => Manager::class,
            'employee' => Employee::class,
            default    => null,
        };
        if (!$model) {
            abort(400, 'نوع المستخدم غير صحيح.');
        }
        $record = $model::with('user')->find($id);
        if (!$record) {
            abort(404, 'السجل غير موجود.');
        }
        $user = $record->user;
        $user->is_active = !$user->is_active;
        $user->save();
        return new successResource([]);
    }
}
