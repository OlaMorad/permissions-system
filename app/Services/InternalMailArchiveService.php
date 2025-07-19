<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Path;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\internalMail;
use App\Enums\StatusInternalMail;
use Illuminate\Support\Facades\DB;
use App\Models\InternalMailArchive;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\successResource;

class InternalMailArchiveService
{

    public function add_to_archive()
    {
        $user = Auth::user();
        $roleName = $user->getRoleNames()->first();

        // منع الموظفين من رؤية الأرشيف
        if (Str::startsWith($roleName, 'موظف')) {
            return response()->json([
                'message' => 'لا يحق لك رؤية الأرشيف'
            ], 403);
        }

        $dateThreshold = Carbon::now()->subHours(48);

        // الرتب المستهدفة بالأرشفة
        $headRoles = [
            'المدير',
            'نائب المدير',
            'رئيس الديوان',
            'رئيس المالية',
            'رئيس مجالس علمية',
            'رئيس الشهادات',
            'رئيس الامتحانات',
            'رئيس الإقامة',
            'رئيس المفاضلة',
        ];

        // 1. أرشفة الرسائل القديمة (تم التحديث قبل 48 ساعة وحالتها APPROVED أو REJECTED)
        $mailsToArchive = InternalMail::with('paths')
            ->where('updated_at', '<=', $dateThreshold)
            ->whereIn('status', [StatusInternalMail::APPROVED, StatusInternalMail::REJECTED])
            ->get();

        foreach ($mailsToArchive as $mail) {
            $pathIds = $mail->paths->pluck('id');

            // جلب معرفات الرتب التي تطابق المسارات والرتب الرئيسية
            $roleIds = Role::whereIn('path_id', $pathIds)
                ->whereIn('name', $headRoles)
                ->pluck('id');

            // جلب معرفات المستخدمين الذين لديهم هذه الرتب
            $userIds = DB::table('model_has_roles')
                ->whereIn('role_id', $roleIds)
                ->where('model_type', \App\Models\User::class)
                ->pluck('model_id');

            // جلب أرقام هواتف هؤلاء المستخدمين
            $phones = User::whereIn('id', $userIds)->pluck('phone')->toArray();

            // إنشاء سجل في الأرشيف
            InternalMailArchive::create([
                'uuid' => $mail->uuid,
                'subject' => $mail->subject,
                'body' => $mail->body,
                'from_user_id' => $mail->from_user_id,
                'to' => $mail->paths->pluck('name')->toArray(),
                'to_phones' => $phones,
                'status' => $mail->status,
                'received_at' => $mail->created_at,
                'created_at' => $mail->created_at,
                'updated_at' => $mail->updated_at,
            ]);

            // حذف الرسالة من الجدول الأصلي
            $mail->delete();
        }

        // 2. تجهيز قائمة المستخدمين المرسلين بناءً على دور المستخدم الحالي (عرض الأرشيف الصادر فقط)
        $fromUserIds = [];

        if (in_array($roleName, ['المدير', 'نائب المدير'])) {
            // المدير أو نائب المدير: فقط رسائله هو شخصياً
            $fromUserIds = [$user->id];
        } elseif (Str::startsWith($roleName, 'رئيس')) {
            // رئيس المكتب: رسائل موظفيه
            $managerId = DB::table('managers')->where('user_id', $user->id)->value('id');

            $fromUserIds = DB::table('employees')
                ->where('manager_id', $managerId)
                ->pluck('user_id')
                ->toArray();
        }

        // 3. جلب الرسائل المؤرشفة التي أرسلها المستخدمون حسب الدور
        $archivedMails = InternalMailArchive::whereIn('from_user_id', $fromUserIds)
            ->get()
            ->map(function ($mail) use ($roleName) {
                $fromUser = User::find($mail->from_user_id);
                $fromRoleName = $fromUser?->getRoleNames()->first();

                $officeName = null;
                $frommanager = null;

                if (in_array($fromRoleName, ['المدير', 'نائب المدير'])) {
                    $officeName = Path::where('id', Role::where('name', $fromRoleName)->value('path_id'))->value('name');
                } else {
                    $employeeRecord = DB::table('employees')->where('user_id', $fromUser->id)->first();
                    $managerRecord = DB::table('managers')->where('id', $employeeRecord?->manager_id)->first();

                    if ($managerRecord) {
                        $officePath = Role::where('id', $managerRecord->role_id)->value('path_id');
                        $officeName = Path::where('id', $officePath)->value('name');
                        $frommanager = User::where('id', $managerRecord->user_id)->first();
                    }
                }

                $result = [
                    'uuid' => $mail->uuid,
                    'phone_from_user' => $frommanager->phone ?? $fromUser->phone ?? null,
                    'subject' => $mail->subject,
                    'name_office' => $officeName,
                    'status' => $mail->status,
                    'sender_at' => $mail->updated_at,
                ];

                // لا تُظهر received_at إذا كان المدير أو نائب المدير
                if (!in_array($roleName, ['المدير', 'نائب المدير'])) {
                    $result['received_at'] = $mail->created_at;
                }

                return $result;
            });

        return new successResource($archivedMails);
    }




    public function show_received_archive()
    {
        $user = Auth::user();

        $roleName = $user->getRoleNames()->first();
        $pathId   = Role::where('name', $roleName)->value('path_id');
        $pathName = Path::where('id', $pathId)->value('name');

        $archivedMails = InternalMailArchive::whereJsonContains('to', $pathName)
            ->get()
            ->map(function ($mail) {
                $fromUser     = User::find($mail->from_user_id);
                $fromRoleName = optional($fromUser)->getRoleNames()->first();

                // إذا كان المرسل "مدير" أو "نائب المدير"
                if (in_array($fromRoleName, ['المدير', 'نائب المدير'])) {
                    $officeName = Path::where('id', Role::where('name', $fromRoleName)->value('path_id'))
                        ->value('name');

                    return $this->formatMailData($mail, $fromUser, $officeName);
                }

                // الحالة العادية (موظف)
                $employeeRecord = DB::table('employees')
                    ->where('user_id', optional($fromUser)->id)
                    ->first();

                $managerUser = null;
                $officeName  = null;

                if ($employeeRecord?->manager_id) {
                    $managerRecord = DB::table('managers')
                        ->where('id', $employeeRecord->manager_id)
                        ->first();

                    if ($managerRecord?->user_id) {
                        $managerUser = User::find($managerRecord->user_id);
                    }

                    if ($managerRecord?->role_id) {
                        $officeName = Path::where(
                            'id',
                            Role::where('id', $managerRecord->role_id)->value('path_id')
                        )->value('name');
                    }
                }

                return $this->formatMailData($mail, $managerUser, $officeName);
            });

        return new successResource($archivedMails);
    }

    /**
     * تنسيق بيانات البريد للعرض.
     */
    private function formatMailData($mail, $user, $officeName)
    {
        return [
            'uuid'            => $mail->uuid,
            'avatar'          => optional($user)->avatar ? asset('storage/' . $user->avatar) : null,
            'phone_from_user' => optional($user)->phone,
            'from_user'       => optional($user)->name,
            'subject'         => $mail->subject,
            'received_at'     => $mail->received_at,
            'name_office'     => $officeName,
        ];
    }



    //عرض البريد ارشيف البريد الصادر للمدير و نائبه حسب اسم الدائرة الممرر
    public function show_sent_archive_for_director($pathName)
    {
        $user = Auth::user();
        $pathId = Path::where('name', $pathName)->value('id');

        // جلب معرفات الرتب التابعة للمسار المحدد
        $roleIds = Role::where('path_id', $pathId)->pluck('id');

        // جلب المستخدمين الذين يملكون هذه الرتب
        $userIds = DB::table('model_has_roles')
            ->whereIn('role_id', $roleIds)
            ->pluck('model_id');

        $archivedMails = InternalMailArchive::whereIn('from_user_id', $userIds)
            ->get()
            ->map(function ($mail) use ($pathName) {
                $fromUser = User::find($mail->from_user_id);
                $fromRoleName = $fromUser?->getRoleNames()->first();

                $finalFromUser = $fromUser;

                // إذا لم يكن المدير أو نائبه، نرجع مديره بدلاً منه
                if (!in_array($fromRoleName, ['المدير', 'نائب المدير'])) {
                    $employeeRecord = DB::table('employees')
                        ->where('user_id', $fromUser?->id)
                        ->first();

                    if ($employeeRecord?->manager_id) {
                        $managerUserId = DB::table('managers')
                            ->where('id', $employeeRecord->manager_id)
                            ->value('user_id');

                        $finalFromUser = User::find($managerUserId);
                    }
                }

                return [
                    'uuid' => $mail->uuid,
                    'subject' => $mail->subject,
                    'from_user' => $finalFromUser?->name,
                    'phone_from_user' => $finalFromUser?->phone,
                    'name_office' => $pathName,
                    'sender_at' => $mail->updated_at,
                ];
            });

        return new successResource($archivedMails);
    }


//عرض الارشيف الوارد للمدير ونائبه حسب المسار الممرر
    public function show_received_archive_for_director($pathName)
    {
        $user = Auth::user();
        $archivedMails = InternalMailArchive::whereJsonContains('to', $pathName)
            ->get()
            ->map(function ($mail) {
                $fromUser = User::find($mail->from_user_id);
                $fromRoleName = optional($fromUser)->getRoleNames()->first();
                $officeName = null;

                if (in_array($fromRoleName, ['المدير', 'نائب المدير'])) {
                    $officeName = Path::where('id', Role::where('name', $fromRoleName)->value('path_id'))->value('name');
                    return $this->formatMailData($mail, $fromUser, $officeName);
                }

                // موظف عادي
                $employeeRecord = DB::table('employees')->where('user_id', optional($fromUser)->id)->first();
                $managerUser = null;

                if ($employeeRecord?->manager_id) {
                    $managerRecord = DB::table('managers')->where('id', $employeeRecord->manager_id)->first();
                    $managerUser = User::find($managerRecord?->user_id);

                    $officeName = Path::where('id', Role::where('id', $managerRecord?->role_id)->value('path_id'))->value('name');
                }

                return $this->formatMailData($mail, $managerUser ?? $fromUser, $officeName);
            });

        return new successResource($archivedMails);
    }
}
