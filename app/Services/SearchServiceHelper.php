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

class SearchServiceHelper
{
     public function fetchFormattedExamRequests($doctorIds, array $statuses, bool $includeStatus)
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
                $elementValues = $request->formContent->elementValues;

                $specialization = $elementValues
                    ->firstWhere('formElement.label', 'يرجى الموافقة على دخولي الاختبار النهائي لاختصاص')?->value;

                $patientName = $elementValues->first()?->value;

                return [
                    'اسم الطبيب'     => $request->doctor->user->name,
                    'رقم الطلب' => $request->uuid,
                    'الاختصاص'       => $specialization,
                    'اسم النموذج'    => $request->formContent->form->name ?? null,
                    'تاريخ التقديم'    => $request->created_at->format('Y-m-d'),
                    'صورة الطبيب'   => optional($request->doctor->user->avatar)
                        ? asset('storage/' . $request->doctor->user->avatar)
                        : null,
                    'رقم الطبيب' => $request->doctor->user->phone,
                    'حالة الطلب'     => $includeStatus ? $request->status : null,
                ];
            });
    }




    public function attachExamDates($requests)
    {
        $specializationNames = $requests->pluck('الاختصاص')->filter()->unique();

        $specializationMap = Specialization::whereIn('name', $specializationNames)->pluck('id', 'name');

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
