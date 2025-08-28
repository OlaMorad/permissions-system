<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Form;
use App\Models\Path;
use App\Models\Role;
use App\Models\User;
use App\Models\Candidate;
use App\Models\ExamRequest;
use App\Enums\ExamRequestEnum;
use App\Models\Specialization;
use App\Http\Resources\failResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\successResource;
use App\Models\Announcement;
use App\Models\ArchiveTransaction;
use App\Models\Transaction;

class SearchService
{
    public function __construct(protected EmployeeService $employeeService, protected SearchServiceHelper $helper) {}
    public function Search_degree_doctor($request)
    {
        $search = Candidate::where('degree', $request)->get();
        return new successResource([$search]);
    }

    public function Search_Exam_Request($request)
    {
        // جلب معرفات الأطباء المرتبطين بالمستخدمين حسب الاسم
        $doctorIds = User::with('doctor')
            ->where('name', 'LIKE', '%' . $request . '%')
            ->get()
            ->filter(fn($user) => $user->doctor)
            ->pluck('doctor.id');

        if ($doctorIds->isEmpty()) {
            return response()->json(['message' => 'لا يوجد أطباء مرتبطين'], 404);
        }

        // الطلبات الواردة والمنتهية
        $importRequests = $this->helper->fetchFormattedExamRequests($doctorIds, [ExamRequestEnum::PENDING->value], true);
        $endRequests    = $this->helper->fetchFormattedExamRequests($doctorIds, [ExamRequestEnum::APPROVED->value, ExamRequestEnum::REJECTED->value], true);

        // إلحاق تواريخ الامتحانات للطلبات المنتهية فقط
        $endRequestsWithDates = $this->helper->attachExamDates($endRequests);

        // دمج النتائج وإرجاعها
        return new successResource($importRequests->concat($endRequestsWithDates));
    }


    public function Search_Specialization_Name($request)
    {
        $search = Specialization::where('name', 'LIKE', '%' . $request . '%')->get();
        return new successResource([$search]);
    }

    public function Search_Employee($request)
    {
        $authUser = auth()->user();
        $authRoleName = $authUser->getRoleNames()->first();

        if (!$authRoleName) {
            return new failResource([]);
        }

        $isPresident = str_starts_with($authRoleName, 'رئيس');

        $authPathId = null;

        if ($isPresident) {
            $authPathId = Role::where('name', $authRoleName)->value('path_id');
            if (!$authPathId) {
                return new failResource([]);
            }
        }

        $stats = $this->employeeService->employeeStatistics();

        $query = User::with(['employee.role', 'manager.role'])
            ->where('name', 'LIKE', '%' . $request . '%');

        if ($isPresident) {
            // الرئيس يبحث فقط عن الموظفين المرتبطين به بنفس الـ path
            $query->whereHas('employee.role', function ($q) use ($authPathId) {
                $q->where('path_id', $authPathId);
            });
        } else {
            // المدير أو النائب يمكنه رؤية الموظفين والمدراء
            $query->where(function ($q) {
                $q->whereHas('employee')->orWhereHas('manager');
            });
        }

        $results = $query
            ->select('id', 'name', 'phone', 'avatar', 'is_active', 'created_at')
            ->get()
            ->map(function ($user) use ($stats) {
                $employee = $user->employee ?? $user->manager;
                if (!$employee) {
                    return null;
                }

                $employeeId = $employee->id;
                $pathId = Path::where('id', $employee->role->path_id)->first();
                return [
                    'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'role' => $employee->role?->name,
                    'handled_transactions' => $stats[$employeeId]['handled_transactions'] ?? 0,
                    'date_jion' => $user->created_at,
                    'is_active' => $user->is_active,
                    'path' => $pathId->name
                ];
            })
            ->filter() // إزالة null
            ->values(); // ترتيب

        return new successResource([$results]);
    }

    public function Search_Form($request)
    {
        $search = Form::where('name', 'LIKE', '%' . $request . '%')->get();
        return new successResource([$search]);
    }

    public function Search_Announcements($request)
    {
        $search = Announcement::where('title', 'LIKE', '%' . $request . '%')
            ->orwhere('body', 'LIKE', '%' . $request . '%')->get();
        return new successResource([$search]);
    }

    public function Search_Archive($request) {}

    // بحث في المعاملات
    public function TransactionSearch($key)
    {
        if (empty($key)) {
            return new failResource('Search key is required');
        }

        $transactions = Transaction::search($key)->get();

        return new successResource($transactions);
    }
    // بحث في ارشيف المعاملات
    public function Archive_Transaction_Search($key)
    {
        if (empty($key)) {
            return new failResource('Search key is required');
        }

        // البحث عبر Scout على ArchiveTransaction
        $archives = ArchiveTransaction::search($key)->get();

        return new successResource($archives);
    }
}
