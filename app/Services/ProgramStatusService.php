<?php

namespace App\Services;

use App\Models\Program;
use App\Enums\ExamRequestEnum;
use App\Enums\Program_ExamStatus;
use App\Http\Resources\successResource;
use Illuminate\Support\Facades\DB;

class ProgramStatusService
{
    public function __construct(protected ExamQuestionAssignmentService $examQuestions){}
    public function updateApprovalStatus(int $programId, string $newStatus)
    {
        $program = Program::findOrFail($programId);

        // تحقق مما إذا كانت الحالة محددة مسبقًا
        if (
            $program->approved === ExamRequestEnum::APPROVED||
            $program->approved === ExamRequestEnum::REJECTED
        ) {
            return new successResource('تم تغيير حالة البرنامج مسبقًا.');
        }
        // تحديث الحالة
        $program->approved = $newStatus;
        // في حال الموافقة، نحدث أيضًا الحالة العامة وحالة الامتحانات المرتبطة
        if ($newStatus === ExamRequestEnum::APPROVED->value) {
            $program->status = Program_ExamStatus::PENDING->value;

            // تحديث حالة جميع الامتحانات المرتبطة دفعة واحدة
            DB::table('exams')
                ->where('program_id', $program->id)
                ->update([
                    'status' => Program_ExamStatus::PENDING->value,
                    'updated_at' => now(),
                ]);
                   $this->examQuestions->assignQuestionsToExams($program->load('exams'));
        }
        $program->save();

        // صياغة الرسالة حسب الحالة الجديدة
        $message = match ($newStatus) {
            ExamRequestEnum::APPROVED->value => 'تمت الموافقة على برنامج الامتحان.',
            ExamRequestEnum::REJECTED->value => 'تم رفض برنامج الامتحان.',
            default => 'تم تحديث حالة البرنامج بنجاح.'
        };
        return new successResource($message);
    }
}
