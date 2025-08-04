<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\User;
use App\Models\Candidate;
use App\Models\ExamRequest;
use App\Enums\ExamRequestEnum;
use App\Models\Specialization;
use App\Http\Resources\successResource;

class SearchService
{

    public function Search_degree_doctor($request)
    {
        $search = Candidate::where('degree', $request)->get();
        return new successResource([$search]);
    }

public function Search_Exam_Request($request)
{
    $doctorIds = User::with('doctor')
        ->where('name', 'LIKE', '%' . $request . '%')
        ->get()
        ->filter(fn($user) => $user->doctor)
        ->pluck('doctor.id');

    if ($doctorIds->isEmpty()) {
        return response()->json(['message' => 'لا يوجد أطباء مرتبطين'], 404);
    }

    $importRequests = $this->formatExamRequests($doctorIds, [ExamRequestEnum::PENDING->value], false);
    $endRequests    = $this->formatExamRequests($doctorIds, [ExamRequestEnum::APPROVED->value, ExamRequestEnum::REJECTED->value], true);
// dd($endRequests);
    // تجهيز تواريخ الامتحانات للطلبات المنتهية
    $endRequestsWithDates = $this->attachExamDates($endRequests);

    return new successResource($importRequests->concat($endRequestsWithDates));
}

private function formatExamRequests($doctorIds, array $statuses, bool $includeStatus)
{
    return ExamRequest::with([
            'formContent.elementValues.formElement',
            'formContent.form',
            'doctor.user'
        ])
        ->whereIn('status', $statuses)
        ->whereIn('doctor_id', $doctorIds)
        ->get()
        ->map(function ($request) use ($includeStatus) {

            // ابحثي عن عنصر الاختصاص داخل elementValues حسب اللابل الخاص به
            $specializationValue = $request->formContent->elementValues
                ->firstWhere('formElement.label', 'يرجى الموافقة على دخولي الاختبار النهائي لاختصاص')?->value;

            $data = [
                'اسم الطبيب'   => $request->doctor->user->name,
                'الاختصاص'     => $specializationValue,
                'اسم النموذج'  => $request->formContent->form->name ?? null,
                'اسم المريض'   => $request->formContent->elementValues->first()?->value ?? null,
                'تاريخ الطلب'  => $request->created_at->format('Y-m-d'),
                'صورة الطبيب' => $request->doctor->user->avatar
                                    ? asset('storage/' . $request->doctor->user->avatar)
                                    : null,
            ];

            if ($includeStatus) {
                $data['حالة الطلب'] = $request->status;
            }

            return $data;
        });
}


private function attachExamDates($requests)
{
    $specializationNames = $requests->pluck('specialization_id')->filter()->unique();
// dd($requests);
    $specializationMap = Specialization::whereIn('id', $specializationNames)->pluck('id', 'name');

    $examDates = Exam::whereIn('specialization_id', $specializationMap->values())
        ->get()
        ->groupBy('specialization_id')
        ->map(fn($exams) => $exams->sortByDesc('created_at')->first()?->date);

    return $requests->map(function ($item) use ($specializationMap, $examDates) {
        $specName = $item['الاختصاص'] ?? null;
        $specId   = $specializationMap[$specName] ?? null;
        $item['تاريخ الامتحان'] = $specId ? $examDates[$specId] ?? null : null;
        return $item;
    });
}

}
