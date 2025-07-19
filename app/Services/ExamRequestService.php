<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Exam;
use App\Models\ExamRequest;
use App\Models\FormContent;
use App\Enums\ExamRequestEnum;
use App\Models\Specialization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\successResource;
use Illuminate\Support\Facades\Storage;

class ExamRequestService
{

    public function create_form_content_exam($data)
    {
        $doctor = Auth::user()->doctor;
        $elements = $data['elements'] ?? [];
        $specializationLabel = null;
        $specializationValue = null;

        // البحث عن مفتاح يحتوي على كلمة "اختصاص"
        foreach ($elements as $label => $value) {
            if (stripos($label, 'اختصاص') !== false) {
                $specializationLabel = $label;
                $specializationValue = $value;
                break;
            }
        }
        // التحقق من أن قيمة الاختصاص موجودة فعلاً عند الطبيب
        if ($specializationValue && !$doctor->specializations->pluck('name')->contains($specializationValue)) {
            throw new \Exception("الاختصاص المدخل غير مسجل لدى الطبيب.");
        }

        if ($specializationValue && $this->isExamTooClose($specializationValue)) {
            throw new \Exception("لا يمكن تقديم الطلب لهذا الاختصاص لأن الامتحان سيبدأ خلال أسبوع.");
        }


        $year = $data['elements']['السنة'] ?? null;

        $cycle = $this->extractCycle($data['elements'] ?? []);

        if ($year && $cycle && $this->hasSubmittedForSameSession($doctor->id, $year, $cycle)) {
            throw new \Exception("لقد قمت بإرسال طلب سابق لنفس السنة والدورة.");
        }

        return DB::transaction(function () use ($data, $doctor) {
            $formContent = FormContent::create([
                'form_id' => $data['form_id'],
                'doctor_id' => $doctor->id,
            ]);

            $this->storeElementValues($formContent, $data['elements'] ?? []);
            $this->storeAttachments($formContent, $data['attachments'] ?? []);

            return $formContent;
        });
    }

    protected function extractCycle(array $elements): ?string
    {
        if (isset($elements['نيسان'])) {
            return 'نيسان';
        }

        if (isset($elements['تشرين الأول'])) {
            return 'تشرين الأول';
        }

        return null;
    }


    //لنخزن قيم الليبلات
    protected function storeElementValues(FormContent $formContent, array $elements): void
    {
        foreach ($elements as $label => $value) {
            $formElement = $formContent->form->elements->firstWhere('label', $label);
            if ($formElement) {
                $formContent->elementValues()->create([
                    'form_element_id' => $formElement->id,
                    'value' => $value,
                ]);
            }
        }
    }


    //تخزين المرفقات
    protected function storeAttachments(FormContent $formContent, array $media): void
    {
        foreach ($media as $label => $files) {
            // تأكدي أن الملفات مصفوفة
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    // خزني الملف واحصلي على المسار
                    $path = $file->store("exam_attachments/files", 'public');

                    //نبحث عن الليبل المدخل في جدول الايلمنت
                    $formElement = $formContent->form->elements->firstWhere('label', $label);
                    if ($formElement) {
                        $formContent->elementValues()->create([
                            'form_element_id' => $formElement->id,
                            'value' => $path,
                        ]);
                    }
                }
            }
        }
    }


    //للتتحقق ان الدكتور نفسو ما قدر يبعت اكتر من طلب لنفس الدورة و السنة
    protected function hasSubmittedForSameSession(int $doctorId, string $year, string $cycle): bool
    {
        return FormContent::where('doctor_id', $doctorId)
            ->whereHas('elementValues', function ($query) use ($year) {
                $query->whereHas('formElement', function ($q) {
                    $q->where('label', 'السنة');
                })->where('value', $year);
            })
            ->whereHas('elementValues', function ($query) use ($cycle) {
                $query->whereHas('formElement', function ($q) use ($cycle) {
                    $q->where('label', $cycle);
                })->where('value', 'on');
            })
            ->exists();
    }



    public function show_form_content_exam($uuid)
    {
        $examRequest = ExamRequest::where('uuid', $uuid)->firstOrFail();
        $formContent = FormContent::where('id', $examRequest->form_content_id)->first();
        if (!$formContent) {
            throw new \Exception("لا يوجد محتوى فورم مرتبط بهذا الطبيب.");
        }

        $elements = [];

        foreach ($formContent->elementValues as $elementValue) {
            $label = $elementValue->formElement->label ?? 'غير معروف';
            $value = $elementValue->value;

            // إذا الملف يبدأ بمسار التخزين، نرجعه كرابط كامل
            if ($value && str_starts_with($value, 'exam_attachments/')) {
                $value = asset('storage/' . $value);
            }
            $elements[] = [
                'label' => $label,
                'value' => $value,
            ];
        }
        return [
            'form_name' => $formContent->form->name,
            'uuid' => $examRequest->uuid,
            'elements' => $elements,
        ];
    }


    public function edit_form_content_exam_status($uuid, $status)
    {
        $examRequest = ExamRequest::where('uuid', $uuid)->update([
            'status' => $status
        ]);
    }
    private function getExamRequestsByStatus(array $statuses, bool $includeStatus = true)
    {
        $examRequests = ExamRequest::with([
            'formContent.form',
            'formContent.elementValues.formElement',
            'doctor.user',
        ])
            ->whereIn('status', $statuses)
            ->get();

        return $examRequests->map(function ($request) use ($includeStatus) {
            $avatarPath = $request->doctor->user->avatar;
            $doctorImageUrl = $avatarPath
                ? url('storage/' . str_replace('\\', '/', ltrim($avatarPath, '/\\')))
                : null;

            // استخراج الاختصاص من بيانات الطلب بناء على اسم الحقل
            $specialtyElement = $request->formContent->elementValues->first(function ($elementValue) {
                $label = $elementValue->formElement->label ?? '';
                return stripos($label, 'اختصاص') !== false;
            });

            $specialty = $specialtyElement ? $specialtyElement->value : null;

            $data = [
                'رقم الطلب' => $request->uuid,
                'اسم الطبيب' => $request->doctor->user->name ?? 'غير معروف',
                'صورة الطبيب' => $doctorImageUrl,
                'رقم الطبيب' => $request->doctor->user->phone ?? null,
                'الاختصاص' => $specialty,
                'تاريخ التقديم' => $request->created_at->format('Y-m-d H:i:s'),
                'اسم الطلب' => $request->formContent->form->name ?? 'غير معروف',
            ];

            if ($includeStatus) {
                $data['حالة الطلب'] = $request->status;
            }

            return $data;
        });
    }


    public function show_all_import_request_exam()
    {
        // PENDING فقط دون إظهار حالة الطلب
        $result = $this->getExamRequestsByStatus(
            [ExamRequestEnum::PENDING->value],
            false
        );

        return new successResource($result);
    }

    public function show_all_end_request_exam()
    {
        // APPROVED و REJECTED مع إظهار حالة الطلب
        $result = $this->getExamRequestsByStatus(
            [ExamRequestEnum::APPROVED->value, ExamRequestEnum::REJECTED->value],
            true
        );

        return new successResource($result);
    }

    // لحتى نوقف استقبال الطلبات بالنسبة للاختصاص اذا ضل للامتحان اسبوع
    protected function isExamTooClose(string $specializationName): bool
    {
        $specialization = Specialization::where('name', $specializationName)->first();

        if (!$specialization) {
            return false; // إذا لم نجد الاختصاص لا نمنع الطلب
        }

        $today = Carbon::today();

        $exam = Exam::where('specialization_id', $specialization->id)
            ->whereDate('date', '>=', $today)
            ->orderBy('date')
            ->first();

        if (!$exam) {
            return false; // لا يوجد امتحان قريب، نسمح بالإرسال
        }

        // إذا الفرق بين التاريخ أقل أو يساوي 7 أيام نمنع
        return Carbon::parse($today)->diffInDays($exam->date) <= 7;
    }
}
