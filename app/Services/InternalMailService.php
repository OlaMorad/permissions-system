<?php

namespace App\Services;

use App\Models\Path;
use App\Models\Role;
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


 private function getUserPathId($userRole)
    {
        if (is_string($userRole)) {
            $role = DB::table('roles')->where('name', $userRole)->first();
        } else {
            $role = $userRole;
        }

        return $role?->path_id ?? null;
    }


    public function create_internal_mail($request)
    {

        $currentUser = Auth::user();
        $admin = $this->is_admin();
        $userRole = $currentUser->getRoleNames()->first();
        // إذا لم يكن Admin أو Sub Admin، نرجع إلى جدول employees

        if (!$admin) {
            $currentEmployee = Employee::where('user_id', $currentUser->id)->first();
            if (!$currentEmployee) {

                return response()->json(['message' => 'لا يمكن العثور على بيانات الموظف.'], 404);
            }

            $userRole = DB::table('roles')
                ->where('id', $currentEmployee->role_id)
               -> first();
        }

        if (!$userRole) {
            return response()->json(['message' => 'لا يمكن تحديد دور المستخدم.'], 403);
        }

        $pathId = $this->getUserPathId($userRole);
        if (!$pathId) {
            return response()->json(['message' => 'لم يتم العثور على المسار الخاص بالمستخدم.'], 404);
        }

        // استخراج اسم الدائرة من الرول
        $senderPathName=DB::table('paths')->where('id',$pathId)->first();
        // إيجاد القسم المقابل في جدول paths

        if (!$senderPathName) {
            return response()->json(['message' => 'لم يتم العثور على قسم المستخدم.'], 404);
        }

        $pathIds = $request->input('to_path_ids');

        // إذا لم يتم تحديد أقسام، نرسل لجميع الأقسام عدا قسم المُرسل
        if (empty($pathIds)) {
            $pathIds = Path::where('id', '!=', $senderPathName->id)->pluck('id')->all();
        }

        // تحديد حالة البريد
        $status = in_array($userRole, ['المدير', 'نائب المدير'])
            ? StatusInternalMail::APPROVED
            : StatusInternalMail::PENDING;

        $mail = InternalMail::create([
            'from_user_id' => $currentUser->id,
            'status'       => $status,
            'subject'      => $request->subject,
            'body'         => $request->body,
        ]);

        // نضيف المسار لجدول الكسر
        $mail->paths()->attach($pathIds);
        return $mail;
    }


public function show_internal_mails_export()
{
    $currentUser = Auth::user();
    $userRole = $currentUser->getRoleNames()->first();
    $adminRoles = ['المدير', 'نائب المدير'];
    $isAdmin = in_array($userRole, $adminRoles);
    $isManager = false;
    $employeeIds = collect(); // استخدام Collection لسهولة المعالجة

    // محاولة الحصول على بيانات المدير المباشر
    if ($managerData = $this->getCurrentManagerWithRole()) {
        $isManager = true;
        $employeeIds = Employee::where('manager_id', $managerData['manager']->id)->pluck('user_id');
    }
    // إذا لم يكن مديرًا، تحقق إن كان موظفًا
    elseif ($employee = Employee::where('user_id', $currentUser->id)->first()) {
        $employeeIds = Employee::where('manager_id', $employee->manager_id)->pluck('user_id');
    }

    // تحميل الرسائل الصادرة فقط من الموظفين التابعين أو من المدير الأعلى نفسه (في حال كان Admin)
    $mails = InternalMail::with(['fromUser:id,name', 'paths:id,name'])
        ->where(function ($query) use ($employeeIds, $currentUser, $isAdmin) {
            $query->whereIn('from_user_id', $employeeIds);

            if ($isAdmin) {
                $query->orWhere('from_user_id', $currentUser->id);
            }
        })
        ->whereIn('status', [
            StatusInternalMail::APPROVED->value,
            StatusInternalMail::REJECTED->value,
        ])
        ->select('id','uuid', 'subject', 'created_at', 'updated_at', 'from_user_id', 'status')
        ->get();

    // الرتب المستهدفة بالهاتف
    $headRoles = [
        'المدير', 'نائب المدير',
        'رئيس الديوان', 'رئيس المالية',
        'رئيس مجالس علمية', 'رئيس الشهادات',
        'رئيس الامتحانات', 'رئيس الإقامة', 'رئيس المفاضلة',
    ];

    // تنسيق النتائج
    $dataFormatted = $mails->map(function ($mail) use ($headRoles, $isManager) {
        $sendDate = $mail->status === StatusInternalMail::APPROVED
            ? $mail->updated_at->toDateTimeString()
            : null;

        $pathIds = $mail->paths->pluck('id');

        $roleIds = Role::whereIn('path_id', $pathIds)
            ->whereIn('name', $headRoles)
            ->pluck('id');

        $userIds = DB::table('model_has_roles')
            ->whereIn('role_id', $roleIds)
            ->where('model_type', \App\Models\User::class)
            ->pluck('model_id');

        $phones = User::whereIn('id', $userIds)->pluck('phone');

        $result = [
            'uuid' => $mail->uuid,
            'subject' => $mail->subject,
            'sender_at' => $sendDate,
            'status' => $mail->status,
            'to' => $mail->paths->pluck('name'),
            'to_phones' => $phones,
        ];

        if ($isManager) {
            $result['received_at'] = $mail->created_at;
        }

        return $result;
    });

    return response()->json([
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
            'mail' => collect($mail)->except('id'),
        ]);
    }

 public function show_import_internal_mails()
{
    $pathData = $this->get_path_name();
    if (!$pathData['path_name']) {
        return response()->json(['message' => 'المسار الخاص بدور المستخدم غير معرف.'], 403);
    }

    if (!$pathData['path_id']) {
        return response()->json(['message' => 'المسار غير موجود في قاعدة البيانات.'], 404);
    }

    // تحميل الرسائل الموافق عليها
    $approvedMails = InternalMail::with(['fromUser:id,name,phone,avatar'])
        ->where('status', StatusInternalMail::APPROVED)
        ->select('id','uuid', 'from_user_id', 'subject', 'updated_at')
        ->get();

    // جلب الرسائل المرتبطة بالمسار الحالي
    $mailIdsForPath = DB::table('internal_mail_paths')
        ->where('path_id', $pathData['path_id'])
        ->whereIn('internal_mail_id', $approvedMails->pluck('id'))
        ->pluck('internal_mail_id')
        ->unique();

    $filteredMails = $approvedMails->filter(function ($mail) use ($mailIdsForPath) {
        return $mailIdsForPath->contains($mail->id);
    });

    return $filteredMails->map(function ($mail) {
        $user = $mail->fromUser;
        $roleName = $user->getRoleNames()->first();

        // جلب path_id من جدول roles
        $pathId = Role::where('name', $roleName)->value('path_id');

        // جلب اسم المكتب من جدول paths
        $officeName = DB::table('paths')->where('id', $pathId)->value('name');

        return [
            'uuid' => $mail->uuid,
            'from_name' => $user->name,
            'from_phone' => $user->phone,
            'from_avatar' => $user->avatar ? asset($user->avatar) : null,
            'from_office' => $officeName,
            'subject' => $mail->subject,
            'received_at' => $mail->updated_at->toDateTimeString(),
        ];
    })->values();
}



public function show_export_internal_mail_details($uuid)
{
    $manager = $this->getCurrentManagerWithRole();
    $is_admin = $this->is_admin();
    $authUserId = Auth::id();

    // جلب البريد باستخدام UUID مع تحميل العلاقات الأساسية
    $mail = InternalMail::with(['fromUser:id,name', 'paths:id,name'])
        ->where('uuid', $uuid)
        ->first();

    if (!$mail) {
        return response()->json(['message' => 'البريد غير موجود.'], 404);
    }

    $is_same_user = $authUserId === $mail->from_user_id;

    // التحقق من الصلاحيات: إذا لم يكن مديرًا ولا أدمنًا ولا نفس المرسل، امنع الدخول
    if (!$manager && !$is_admin && !$is_same_user) {
        return response()->json(['message' => 'لا تملك صلاحية عرض هذا البريد.'], 403);
    }

    // إذا كان مديرًا، تأكد أن الموظف من ضمن موظفيه
    if ($manager && !$is_same_user) {
        $employeeIds = Employee::where('manager_id', $manager['manager']->id)->pluck('user_id')->toArray();
        if (!in_array($mail->from_user_id, $employeeIds)) {
            return response()->json(['message' => 'هذا البريد لا يخص موظفيك.'], 403);
        }
    }

        // التحقق من صلاحية المسؤول (admin)
    if ($is_admin && $mail->from_user_id !== $authUserId) {
        return response()->json(['message' => 'هذا البريد لا يخصك.'], 403);
    }

    // الرتب التي تستهدف أرقام هواتفها (كما في التابع السابق)
    $headRoles = [
        'المدير', 'نائب المدير',
        'رئيس الديوان', 'رئيس المالية',
        'رئيس مجالس علمية', 'رئيس الشهادات',
        'رئيس الامتحانات', 'رئيس الإقامة', 'رئيس المفاضلة',
    ];

    // جلب معرفات الدوائر (paths)
    $pathIds = $mail->paths->pluck('id');

    // جلب معرفات الرتب
    $roleIds = Role::whereIn('path_id', $pathIds)
        ->whereIn('name', $headRoles)
        ->pluck('id');

    // المستخدمون المرتبطون بهذه الرتب
    $userIds = DB::table('model_has_roles')
        ->whereIn('role_id', $roleIds)
        ->where('model_type', \App\Models\User::class)
        ->pluck('model_id');

    $phones = User::whereIn('id', $userIds)->pluck('phone');

    return response()->json([
        'uuid' => $mail->uuid,
        'subject' => $mail->subject,
        'body' => $mail->body,
        'sender' => $mail->fromUser->name ?? null,
        'status' => $mail->status,
        'sent_at' => $mail->updated_at->toDateTimeString(),
        'to' => $mail->paths->pluck('name'),
        'to_phones' => $phones,
    ]);
}

    //التحقق اذا كان المستخدم الحالي ادمن او سب ادمن
    public function is_admin()
    {

        $currentUser = Auth::user();
        $userRole = $currentUser->getRoleNames()->first();
        $adminRoles = ['المدير', 'نائب المدير'];
        if (in_array($userRole, $adminRoles))
            return true;
    }

public function show_import_internal_mail_details($uuid)
{
    $pathData = $this->get_path_name();
    if (!$pathData['path_name']) {
        return response()->json(['message' => 'المسار الخاص بدور المستخدم غير معرف.'], 403);
    }
    if (!$pathData['path_id']) {
        return response()->json(['message' => 'المسار غير موجود في قاعدة البيانات.'], 404);
    }

    // جلب معرف البريد بناءً على الـ UUID
    $mail = InternalMail::where('uuid', $uuid)->first();
    if (!$mail) {
        return response()->json(['message' => 'البريد غير موجود.'], 404);
    }

    // تأكد أن البريد مرتبط بمسار المستخدم
    $isPathLinked = DB::table('internal_mail_paths')
        ->where('internal_mail_id', $mail->id)
        ->where('path_id', $pathData['path_id'])
        ->exists();
    if (!$isPathLinked) {
        return response()->json(['message' => 'لا تملك صلاحية لرؤية هذا البريد.'], 403);
    }

    // جلب البريد مع المرسل
    $mail = InternalMail::with('fromUser:id,name,avatar,phone')
        ->where('uuid', $uuid)
        ->where('status', StatusInternalMail::APPROVED)
        ->first();

    if (!$mail) {
        return response()->json(['message' => 'البريد غير موجود أو لم يتم الموافقة عليه.'], 404);
    }

    $user = $mail->fromUser;
    $senderRole = $user->getRoleNames()->first() ?? 'غير معروف';
    $officeName = $this->filter_office_name($senderRole);

    return response()->json([
        'uuid' => $mail->uuid,
        'from_office' => $officeName,
         'from_name' => $user->name,
         'from_phone'=>$user->phone,
         'from_avatar'=> asset($user->avatar),
        'subject' => $mail->subject,
        'body' => $mail->body,
        'received_at' => $mail->updated_at->toDateTimeString(),
    ]);
}



    public function get_path_name()
    {
        $currentUser = Auth::user();
        $roleName = $currentUser->getRoleNames()->first();
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            $pathId = $role->path_id;
            $pathName = Path::where('id', $pathId)->first();

            return [
                'path_name' => $pathName,
                'path_id' => $pathId
            ];
        }
        return null;
    }

    public function filter_office_name($roleName)
    {
        return str_replace([' موظف', ' رئيس'], '', $roleName);
    }
}
