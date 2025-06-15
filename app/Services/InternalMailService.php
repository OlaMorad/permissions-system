<?php

namespace App\Services;

use App\Models\User;
use App\Models\Manager;
use App\Models\Employee;
use App\Models\InternalMail;
use App\Enums\StatusInternalMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InternalMailService
{
    // دالة مساعدة لجلب المدير الحالي مع دوره
    private function getCurrentManagerWithRole()
    {
        $currentUser = Auth::user();
        $manager = DB::table('managers')->where('user_id', $currentUser->id)->first();
        if (!$manager) return null;

        $role = DB::table('roles')->where('id', $manager->role_id)->first();
        return ['manager' => $manager, 'role' => $role];
    }

    // تحقق إذا المستخدم هو مدير ورقمه يبدأ بـ Head
    private function isHeadManager($role): bool
    {
        return $role && str_starts_with($role->name, 'Head');
    }

    public function create_internal_mail($request)
    {
        $currentUser = Auth::user();
        $targetUser = User::find($request->to_user_id);

        if (!$targetUser) {
            return response()->json(['message' => 'المستخدم المستلم غير موجود.'], 404);
        }

        $adminRoles = ['Admin', 'Sub Admin'];
        $status = in_array($currentUser->name, $adminRoles)
            ? StatusInternalMail::APPROVED
            : StatusInternalMail::PENDING;

        $mail = InternalMail::create([
            'from_user_id' => $currentUser->id,
            'to_user_id' => $targetUser->id,
            'status' => $status,
            'subjcet' => $request->subject,
            'body' => $request->body,
        ]);

        return [
            'to_user_id' => $mail->to_user_id,
            'status' => $mail->status,
            'subject' => $mail->subject,
            'body' => $mail->body,
        ];
    }

public function show_internal_mails_by_status($status)
{
    try {
        $statusEnum = StatusInternalMail::from($status);
    } catch (\ValueError) {
        return response()->json(['message' => 'الحالة غير صحيحة.'], 422);
    }

    $data = $this->getCurrentManagerWithRole();
    if (!$data) {
        return response()->json(['message' => 'أنت لست مديراً.'], 403);
    }

    if (!$this->isHeadManager($data['role'])) {
        return response()->json(['message' => 'ليس لديك صلاحية.'], 403);
    }

    $employeeIds = Employee::where('manager_id', $data['manager']->id)->pluck('user_id');

    $mails = InternalMail::with('toUser:id,name') // علاقة لجلب اسم المستلم
        ->whereIn('from_user_id', $employeeIds)
        ->where('status', $statusEnum->value)
        ->select('id', 'to_user_id', 'subjcet', 'updated_at', 'from_user_id') // نختار الحقول المطلوبة + from_user_id لأننا بنستخدمه داخل الـ with عشان العلاقة
        ->get();

    $dataFormatted = $mails->map(function ($mail) {
        return [
            'id' => $mail->id,
            'subject' => $mail->subjcet,
            'to_user_name' => $mail->toUser->name ?? 'غير معروف',
            'updated_at' => $mail->updated_at->toDateTimeString(),
        ];
    });

    return response()->json([
        'status' => $statusEnum->value,
        'mails' => $dataFormatted,
    ]);
}


    public function edit_status_internal_mails($request)
    {
        $currentUser = Auth::user();
        $manager = DB::table('managers')->where('user_id', $currentUser->id)->first();

        if (!$manager) {
            return response()->json(['message' => 'ليس لديك صلاحية تعديل حالة هذا البريد. (أنت لست مديرًا)'], 403);
        }

        $mail = InternalMail::find($request->id);
        if (!$mail) {
            return response()->json(['message' => 'البريد المطلوب غير موجود.'], 404);
        }

        $employee = DB::table('employees')
            ->where('user_id', $mail->from_user_id)
            ->where('manager_id', $manager->id)
            ->first();

        if (!$employee) {
            return response()->json(['message' => 'لا يمكنك تعديل هذه الرسالة لأنها لا تخص أحد موظفيك.'], 403);
        }

        $mail->status = $request->status;
        $mail->save(); // هذا سيحدث updated_at تلقائيًا

        return response()->json([
            'message' => 'تم تحديث حالة البريد بنجاح.',
            'mail' => $mail,
        ]);
    }

    public function show_import_internal_mails()
    {
        $currentUser = Auth::user();

        $mails = InternalMail::with('fromUser:id,name')
            ->where('to_user_id', $currentUser->id)
            ->where('status', StatusInternalMail::APPROVED)
            ->select('id', 'from_user_id', 'subjcet', 'body', 'updated_at')
            ->get();

return $data = $mails->map(function ($mail) {
    $user = $mail->fromUser;
    $roleName = $user->getRoleNames()->first() ?? 'غير معروف';

    // نحذف الكلمات مثل "User" أو "Officer" إذا وجدت
    $officeName = str_replace([' User', ' Officer'], '', $roleName);

    return [
        'id' => $mail->id,
        'from_office' => $officeName,
        'subjcet' => $mail->subjcet,
        'body' => $mail->body,
        'received_at' => $mail->updated_at->toDateTimeString(),
    ];
});

    }
}
