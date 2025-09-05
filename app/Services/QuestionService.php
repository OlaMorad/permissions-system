<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Models\QuestionBank;
use Illuminate\Http\Request;
use App\Imports\QuestionsImport;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\failResource;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Crypt;
use App\Interfaces\QuestioneInterface;
use App\Jobs\ImportQuestionsFromExcel;
use App\Http\Resources\successResource;

class QuestionService implements QuestioneInterface
{    public function __construct(protected FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }
    public function addFromForm($request)
    {
        $plainQuestion = $request->question;
        $questionHash = hash('sha256', $plainQuestion);
        $firebase = app(FirebaseNotificationService::class);
        // التحقق من عدم وجود سؤال مكرر
        if (QuestionBank::where('question_hash', $questionHash)->exists()) {
            $firebase->sendToRole(
                'رئيس الامتحانات',
                'فشل إضافة سؤال',
                "السؤال '{$plainQuestion}' موجود مسبقاً ولم يتم إضافته.",
                ['question' => $plainQuestion,
                   'action_required' => true,
                   'type'=>'question_bank'
                   ]
            );
            return response()->json(['message' => 'السؤال موجود مسبقاً']);
        }


        $question = QuestionBank::create([
            'question'          =>  Crypt::encryptString($request->question),
            'specialization_id' => $request->specialization_id,
            'option_a'          => Crypt::encryptString($request->option_a),
            'option_b'          =>  Crypt::encryptString($request->option_b),
            'option_c'          =>  Crypt::encryptString($request->option_c),
            'option_d'          =>  Crypt::encryptString($request->option_d),
            'correct_answer'    => Crypt::encryptString($request->correct_answer),
            'difficulty_level'  => $request->difficulty_level,
            'question_hash'     => $questionHash,
        ]);
        $firebase->sendToRole(
            'المدير',
            'تمت إضافة سؤال جديد',
            "تمت إضافة السؤال '{$plainQuestion}'.",
            ['question_id' => $question->id]
        );
        return new successResource('تمت إضافة السؤال بنجاح');
    }

    public function addFromExcel($request): void
    {
        $path = $request->file('file')->storeAs(
            'temp_imports',
            uniqid() . '_' . $request->file('file')->getClientOriginalName()
        );
        $batchId = (string) Str::uuid();
        ImportQuestionsFromExcel::dispatch($path, $request->specialization_id, $batchId);
    }

    public function updateQuestionStatus(array $validated, string $status)
    {
        try {
            if (isset($validated['question_id'])) {
                // تحديث سؤال مفرد
                $question = QuestionBank::findOrFail($validated['question_id']);
                $question->status = $status;
                $question->save();
                // إرسال إشعار لرئيس الامتحانات
                $this->firebaseService->sendToRole(
                    'رئيس الامتحانات',
                    'تم تعديل حالة سؤال',
                    "تم تحديث السؤال (ID: {$question->id}) إلى الحالة: {$status}"
                );
            return new successResource('تم تحديث حالة السؤال بنجاح');

         } elseif (isset($validated['batch_id'])) {
                // تحديث مجموعة أسئلة بنفس الـ batch_id
                $updated = QuestionBank::where('batch_id', $validated['batch_id'])
                    ->update(['status' => $status]);

                if ($updated > 0) {
                    $this->firebaseService->sendToRole(
                        'رئيس الامتحانات',
                        'تم تعديل حالة مجموعة أسئلة',
                        "تم تحديث {$updated} سؤال (Batch ID: {$validated['batch_id']}) إلى الحالة: {$status}"
                    );
                }
                            return new successResource('تم تحديث حالة السؤال بنجاح');

            }

        } catch (\Throwable $e) {
            Log::error("فشل تحديث حالة السؤال/الأسئلة", [
                'error' => $e->getMessage(),
                'data' => $validated,
            ]);
        return new failResource(['فشل التحديث']);
        }
    }
}
