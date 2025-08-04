<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Exam;
use App\Models\Form;
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
    public function __construct(protected ExamRequestServiceHelper $helper) {}


    public function create_form_content_exam($data)
    {
        $doctor = Auth::user()->doctor;
        $elements = $data['elements'] ?? [];
        $specializationLabel = null;
        $specializationValue = null;
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
//اذا ضل اسبوع او اقل للامتحان نمنع ارسال الطلبات
        if ($specializationValue && $this->helper->isExamTooClose($specializationValue)) {
            throw new \Exception("لا يمكن تقديم الطلب لهذا الاختصاص لأن الامتحان سيبدأ خلال أسبوع.");
        }
        $specialization = Specialization::where('name', $specializationValue)->first();
        $year = $data['elements']['السنة'] ?? null;
        $cycle = $data['elements']['دورة'] ?? null;

        if (is_array($year)) {
            $year = $year[0];
        }
        if (is_array($cycle)) {
            $cycle = $cycle[0];
        }
        $cycle = $this->helper->extractCycle($data['elements'] ?? []);
        $form = Form::find($data['form_id']);
        $formName = $form?->name ?? '';


        if (str_contains($formName, 'اعتذار')) {
            // تحقق إذا سبق وقدم اعتذار بنفس السنة والدورة والاختصاص
            if ($this->helper->hasPreviousApologyRequest($doctor->id, $year, $cycle, $form->id, $specialization->name)) {
                throw new \Exception("لقد قدمت طلب اعتذار سابق بنفس السنة والدورة و الاختصاص .");
            }

            // تحقق وجود طلب ترشيح سابق بنفس المواصفات
            if ($this->helper->checkNominationRequestPrecondition($data['form_id'], $doctor->id, $specialization->id, $year,  $cycle)) {
            }
        } else {
            // طلب ترشيح

            if ($this->helper->hasSubmittedForSameSession($doctor->id, $year, $cycle, $form->id, $specialization->name)) {
                throw new \Exception("لقد قمت بإرسال طلب ترشيح سابق لنفس السنة والدورة.");
            }
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





    //لنخزن قيم الليبلات
    protected function storeElementValues(FormContent $formContent, array $elements): void
    {
        foreach ($elements as $label => $value) {
            $formElement = $formContent->form->elements->firstWhere('label', $label);
            if ($formElement) {
                if (is_array($value)) {
                    // إذا القيمة مصفوفة، خزّن كل عنصر في صف مستقل
                    foreach ($value as $singleValue) {
                        $formContent->elementValues()->create([
                            'form_element_id' => $formElement->id,
                            'value' => $singleValue,
                        ]);
                    }
                } else {
                    // القيمة مفردة
                    $formContent->elementValues()->create([
                        'form_element_id' => $formElement->id,
                        'value' => $value,
                    ]);
                }
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



//عرض الطلب بناء عال uuid
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

//الموافقة او رفض الطلب من قبل موظف الامتحانات
    public function edit_form_content_exam_status($uuid, $status)
    {
        $examRequest = ExamRequest::where('uuid', $uuid)->first();
        if ($examRequest) {
            $examRequest->update([
                'status' => $status
            ]);
        }
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

//عرض الطلبات الواردة يلي لسا حالتها قيد الدراسة
    public function show_all_import_request_exam()
    {
        // PENDING فقط دون إظهار حالة الطلب
        $result = $this->getExamRequestsByStatus(
            [ExamRequestEnum::PENDING->value],
            false
        );

        return new successResource($result);
    }
//عرض الطلبات المنتهية يلي حالتها تغيرت لمرفوضة او مقبولة
  public function show_all_end_request_exam()
{
    $result = $this->getExamRequestsByStatus(
        [ExamRequestEnum::APPROVED->value, ExamRequestEnum::REJECTED->value],
        true
    );

    $specializationNames = $result->pluck('الاختصاص')->filter()->unique();

    $specializations = Specialization::whereIn('name', $specializationNames)->get();

    $specializationMap = $specializations->pluck('id', 'name');

$examDates = Exam::whereIn('specialization_id', $specializations->pluck('id'))
    ->get()
    ->groupBy('specialization_id')
    ->map(function ($exams) {
        return $exams->sortByDesc('created_at')->first()?->date;
    });


    // نضيف "تاريخ الامتحان" لكل طلب حسب اختصاصه
    $finalResult = $result->map(function ($item) use ($specializationMap, $examDates) {
        $specializationName = $item['الاختصاص'] ?? null;

        if ($specializationName && isset($specializationMap[$specializationName])) {
            $specId = $specializationMap[$specializationName];
            $item['تاريخ الامتحان'] = $examDates[$specId] ?? null;
        } else {
            $item['تاريخ الامتحان'] = null;
        }

        return $item;
    });

    return new successResource($finalResult);
}

}
