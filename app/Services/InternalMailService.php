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
        $data = $this->getCurrentManagerWithRole();
        if (!$data) {
            return response()->json(['message' => 'أنت لست مديراً.'], 403);
        }

        $employeeIds = Employee::where('manager_id', $data['manager']->id)->pluck('user_id');

        $mails = InternalMail::with(['fromUser:id,name', 'paths:id,name']) // أضفنا paths هنا
            ->whereIn('from_user_id', $employeeIds)
            ->select('id', 'subject', 'created_at','updated_at', 'from_user_id','status')
            ->get();

               $dataFormatted = $mails->map(function ($mail) {
        $sendDate = $mail->status === StatusInternalMail::APPROVED
            ? $mail->updated_at->toDateTimeString()
            : null;
               return [
            'id' => $mail->id,
            'subject' => $mail->subject,
            'sender_at' => $sendDate,
            'Received at'=>$mail->created_at,
            'status'=>$mail->status,
            'to' => $mail->paths->pluck('name'),
        ];
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
            'mail' => $mail,
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
        // جلب جميع الإيميلات التي تم الموافقة عليها
        $approvedMails = InternalMail::with('fromUser:id,name')
            ->where('status', StatusInternalMail::APPROVED)
            ->select('id', 'from_user_id', 'subject', 'updated_at')
            ->get();

        // جلب ids الإيميلات التي تحتوي على path_id مطابق
        $mailIdsForPath = DB::table('internal_mail_paths')
            ->where('path_id', $pathData['path_id'])
            ->whereIn('internal_mail_id', $approvedMails->pluck('id')->toArray())
            ->pluck('internal_mail_id')
            ->unique();

        // فلترة الإيميلات بحيث تبقى فقط التي تتوافق مع المسار
        $filteredMails = $approvedMails->filter(function ($mail) use ($mailIdsForPath) {
            return $mailIdsForPath->contains($mail->id);
        });

        return $filteredMails->map(function ($mail) {
            $user = $mail->fromUser;
            $roleName = $user->getRoleNames()->first() ?? 'غير معروف';

            $officeName = $this->filter_office_name($roleName);

            return [
                'id' => $mail->id,
                'from_office' => $officeName,
                'subject' => $mail->subject,
                'received_at' => $mail->updated_at->toDateTimeString(),
            ];
        })->values(); // إعادة ترقيم المفاتيح بشكل مرتب
    }


    public function show_export_internal_mail_details($id)
    {

        $manager = $this->getCurrentManagerWithRole();
        $is_admin = $this->is_admin();

        if (!$manager && !$is_admin) {
            return response()->json(['message' => 'أنت لست مديراً.'], 403);
        }

        if ($manager) {
            $employeeIds = Employee::where('manager_id', $manager['manager']->id)->pluck('user_id');
            $mail = InternalMail::with(['fromUser:id,name', 'paths:id,name'])
                ->whereIn('from_user_id', $employeeIds)
                ->find($id->id);

            if (!$mail) {
                return response()->json(['message' => 'هذا البريد لا يخص موظفيك.'], 403);
            }
        }

        if ($is_admin) {
            $mail = InternalMail::with(['fromUser:id,name', 'paths:id,name'])
                ->whereIn('from_user_id', [Auth::id()])->find($id->id);
        }
        return response()->json([
            'id' => $mail->id,
            'subject' => $mail->subject,
            'body' => $mail->body,
            'sender' => $mail->fromUser->name ?? null,
            'status' => $mail->status,
            'sent_at' => $mail->updated_at->toDateTimeString(),
            'to' => $mail->paths->pluck('name'),
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

    public function show_import_internal_mail_details($request)
    {

        $pathData = $this->get_path_name();
        if (!$pathData['path_name']) {
            return response()->json(['message' => 'المسار الخاص بدور المستخدم غير معرف.'], 403);
        }
        if (!$pathData['path_id']) {
            return response()->json(['message' => 'المسار غير موجود في قاعدة البيانات.'], 404);
        }
        $mailId = $request->id;

        // تأكد أن البريد موجود ومساره يحتوي على path_id للمستخدم
        $isPathLinked = DB::table('internal_mail_paths')
            ->where('internal_mail_id', $mailId)
            ->where('path_id', $pathData['path_id'])
            ->exists();
        if (!$isPathLinked) {
            return response()->json(['message' => 'لا تملك صلاحية لرؤية هذا البريد.'], 403);
        }

        $mail = InternalMail::with('fromUser:id,name')
            ->where('id', $mailId)
            ->where('status', StatusInternalMail::APPROVED)
            ->first();

        if (!$mail) {
            return response()->json(['message' => 'البريد غير موجود أو لم يتمت الموافقة عليه.'], 404);
        }

        $user = $mail->fromUser;
        $senderRole = $user->getRoleNames()->first() ?? 'غير معروف';
        $officeName = $this->filter_office_name($senderRole);
        return response()->json([
            'id' => $mail->id,
            'from_office' => $officeName,
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
