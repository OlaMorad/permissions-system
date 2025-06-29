<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Models\Manager;
use App\Models\Employee;
use App\Models\TransactionMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EmployeeService
{
    public function edit_employee_information( $data)
    {
        $employee = $this->getEmployee($data->employee_id);

        $this->authorizeManager($employee);

        $user = $employee->user;

        $this->updateBasicInformation($user, $data);
        $this->updatePassword($user, $data);
        $this->updateAvatar($user, $data);

        $user->save();

        return $this->formatUserResponse($user);
    }

//الحصول على الموظف المطلوب
    private function getEmployee(int $id): Employee
    {
        return Employee::findOrFail($id);
    }

    //التأكد من ان الموظف تابع لهذا المدير
    private function authorizeManager(Employee $employee)
    {
        $manager = Manager::where('user_id', Auth::id())->first();

        if (!$manager || $employee->manager_id !== $manager->id) {
             abort(403, 'ليس لديك الصلاحية لتعديل هذا الموظف');
        }
    }

    private function updateBasicInformation($user,  $data): void
    {
        $user->name    = $data['name'] ?? $user->name;
        $user->email   = $data['email'] ?? $user->email;
        $user->address = $data['address'] ?? $user->address;
        $user->phone   = $data['phone'] ?? $user->phone;
    }

    private function updatePassword($user,  $data): void
    {
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
    }

    private function updateAvatar($user,  $data): void
    {
        if (!empty($data['avatar'])) {
            if ($user->avatar) {
                //حذف الصورة القديمة
                Storage::disk('public')->delete($user->avatar);
            }

            $user->avatar = $data['avatar']->store('avatars', 'public');
        }
    }

    private function formatUserResponse($user): array
    {
        return [
            'id'      => $user->id,
            'name'    => $user->name,
            'email'   => $user->email,
            'address' => $user->address,
            'phone'   => $user->phone,
            'avatar'  => $user->avatar ? asset('storage/' . $user->avatar) : null,
        ];
    }


    public function employeeStatistics(): array
    {
        // نجيب عدد المعاملات لكل موظف حسب حالة المعاملة (محولة أو مرفوضة فقط)
        return TransactionMovement::with('changedBy')
            ->whereIn('status', [
                TransactionStatus::FORWARDED->value,
                TransactionStatus::REJECTED->value,
            ])
            ->selectRaw('changed_by, COUNT(*) as total')
            ->groupBy('changed_by')
            ->get()
            ->mapWithKeys(function ($movement) {
                // نستخدم employee_id كـ key والقيمة عدد المعاملات
                return [
                    $movement->changed_by => [
                        'handled_transactions' => $movement->total,
                        'employee_name' => $movement->changedBy->name ?? 'غير معروف',
                    ],
                ];
            })
            ->toArray();
    }

    /**
     * عرض كل الموظفين التابعين للمدير مع إحصائياتهم (عدد المعاملات المنتهية)
     */
    public function show_employees()
    {
        // تأكيد أن المستخدم هو مدير
        $user = DB::table('users')->where('id', Auth::id())->first();
        $manager = DB::table('managers')->where('user_id', $user->id)->first();
        if (!$manager) {
            return abort(403, 'انت لست مدير');
        }

        // نجيب إحصائيات الموظفين مرة وحدة لتجنب استعلامات متكررة
        $stats = $this->employeeStatistics();

        // استعلام لربط جدول employees مع جدول users باستخدام join
        $employees = DB::table('employees')
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->where('employees.manager_id', $manager->id)
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.phone',
                'users.avatar',
                'users.address'
            )
            ->get()
            ->map(function ($employee) use ($stats) {
                $employee->avatar = $employee->avatar
                    ? asset('storage/' . $employee->avatar)
                    : null;

                // نضيف إحصائيات عدد المعاملات اللي أنهى الموظف (لو موجودة)
                $employeeStats = $stats[$employee->id]['handled_transactions'] ?? 0;
                $employee->handled_transactions = $employeeStats;

                return $employee;
            });

        return response()->json($employees);
    }

public function convert_employee_status($employeeId)
{
    // 1. التحقق من أن المستخدم الحالي هو مدير
    $manager = Manager::where('user_id', Auth::id())->first();
    if (!$manager) {
        return response()->json(['message' => 'غير مصرح لك بالوصول'], 403);
    }

    // 2. جلب الموظف المراد تعديله والتأكد من أنه يتبع للمدير نفسه
    $employee = Employee::where('id', $employeeId)
        ->where('manager_id', $manager->id)
        ->with('user') // نحتاج بيانات المستخدم المرتبطة
        ->first();

    if (!$employee || !$employee->user) {
        return response()->json(['message' => 'الموظف غير موجود أو لا يتبع لك'], 404);
    }

    // 3. قلب حالة التفعيل
    $employee->user->is_active = !$employee->user->is_active;
    $employee->user->save();

    return response()->json([
        'message' => 'تم تحديث حالة التفعيل بنجاح',
        'is_activity' => $employee->user->is_active
    ]);
}

}
