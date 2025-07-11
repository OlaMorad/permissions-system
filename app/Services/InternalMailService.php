<?php

namespace App\Services;

use App\Models\Path;
use App\Models\Role;
use App\Models\User;
use App\Models\Manager;
use App\Models\Employee;
use App\Traits\LoggerTrait;
use App\Models\InternalMail;
use App\Enums\StatusInternalMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Presenters\InternalMailsPresenter;

class InternalMailService
{
    use LoggerTrait;

        public function __construct(InternalMailsPresenter $presenter)
    {
        $this->presenter = $presenter;
    }

    public function create_internal_mail($request)
    {

        $currentUser = Auth::user();
        $admin = $this->presenter->is_admin();
        $userRole = $currentUser->getRoleNames()->first();
        // إذا لم يكن Admin أو Sub Admin، نرجع إلى جدول employees

        if (!$admin) {
            $currentEmployee = Employee::where('user_id', $currentUser->id)->first();
            if (!$currentEmployee) {

                return response()->json(['message' => 'لا يمكن العثور على بيانات الموظف.'], 404);
            }

            $userRole = DB::table('roles')
                ->where('id', $currentEmployee->role_id)
                ->first();
        }

        if (!$userRole) {
            return response()->json(['message' => 'لا يمكن تحديد دور المستخدم.'], 403);
        }

        $pathId = $this->presenter->getUserPathId($userRole);
        if (!$pathId) {
            return response()->json(['message' => 'لم يتم العثور على المسار الخاص بالمستخدم.'], 404);
        }

        // استخراج اسم الدائرة من الرول
        $senderPathName = DB::table('paths')->where('id', $pathId)->first();
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
        $this->logInfo(' إنشاء بريد داخلي', ['from_user_id' => $currentUser->id]);
        return collect($mail)->except('id');
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
        if ($managerData = $this->presenter->getCurrentManagerWithRole()) {
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
            ->select('id', 'uuid', 'subject', 'created_at', 'updated_at', 'from_user_id', 'status')
            ->get();

        // الرتب المستهدفة بالهاتف
        $headRoles =$this->presenter->headRoles;


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

        $mail = InternalMail::where('uuid', $request->uuid)->firstOrFail();
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

        $this->logInfo(' تعديل بريد داخلي', ['managers_id' => $manager->id]);
        return response()->json([
            'message' => 'تم تحديث حالة البريد بنجاح.',
            'mail' => collect($mail)->except('id'),
        ]);
    }

    public function show_import_internal_mails()
    {
        $pathData = $this->presenter->get_path_name();
        if (!$pathData['path_name']) {
            return response()->json(['message' => 'المسار الخاص بدور المستخدم غير معرف.'], 403);
        }

        if (!$pathData['path_id']) {
            return response()->json(['message' => 'المسار غير موجود في قاعدة البيانات.'], 404);
        }

        // تحميل الرسائل الموافق عليها
        $approvedMails = InternalMail::with(['fromUser:id,name,phone,avatar'])
            ->where('status', StatusInternalMail::APPROVED)
            ->select('id', 'uuid', 'from_user_id', 'subject', 'updated_at')
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
            // في حال كان المرسل هو الموظف نبدله لرئيس
             if(str_starts_with( $roleName,'موظف')){
                 $from = $this->presenter->sender_mail($roleName);
        }
         else {
            $from = $roleName;
        }
            // جلب path_id من جدول roles
            $pathId = Role::where('name', $roleName)->value('path_id');

            // جلب اسم المكتب من جدول paths
            $officeName = DB::table('paths')->where('id', $pathId)->value('name');

            return [
                'uuid' => $mail->uuid,
                'from_name' => $from,
                'from_phone' => $user->phone,
                'from_avatar' => $user->avatar ? asset($user->avatar) : null,
                'from_office' => $officeName,
                'subject' => $mail->subject,
                'received_at' => $mail->updated_at->toDateTimeString(),
            ];
        })->values();
    }


    public function show_internal_mail_details($uuid)
    {
        $currentUser = Auth::user();
        $roleName = $currentUser->getRoleNames()->first();
        if ($roleName == 'الطبيب')
            return abort(403, 'لا يحق لك روؤية البريد');

        $mail = InternalMail::where('uuid', $uuid)->select('subject', 'body', 'updated_at', 'from_user_id')->first();
        $sender = User::where('id', $mail->from_user_id)->first();
        $senderRole = DB::table('model_has_roles')->where('model_id', $sender->id)->first();
        $Role = Role::where('id', $senderRole->role_id)->select('name')->first();
        if (str_starts_with($Role->name, 'موظف')) {
            $from = $this->presenter->sender_mail($Role->name);
        } else {
            $from = $Role;
        }
        $mail->from = $from;
        return  $mail;
    }
}
