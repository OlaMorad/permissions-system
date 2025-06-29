<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\internalMail;
use App\Enums\StatusInternalMail;
use Illuminate\Support\Facades\DB;
use App\Models\InternalMailArchive;

class InternalMailArchiveService{


public function add_to_archive()
{
    $dateThreshold = Carbon::now()->subMinute(4);

    // الرتب المستهدفة بالهاتف كما في دالة show_internal_mails_export
    $headRoles = [
        'المدير', 'نائب المدير',
        'رئيس الديوان', 'رئيس المالية',
        'رئيس مجالس علمية', 'رئيس الشهادات',
        'رئيس الامتحانات', 'رئيس الإقامة', 'رئيس المفاضلة',
    ];

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
        $phonesJson = json_encode($phones);

        // جمع معرفات الدوائر كـ JSON
        $toJson = json_encode($mail->paths->pluck('id')->toArray());

        InternalMailArchive::create([
            'uuid' => $mail->uuid,
            'subject' => $mail->subject,
            'body' => $mail->body,
            'from_user_id' => $mail->from_user_id,
            'to' => $toJson,
            'to_phones' => $phonesJson,
            'status' => $mail->status,
            'created_at' => $mail->created_at,
            'updated_at' => $mail->updated_at,
        ]);

        $mail->delete();
    }
}


}
