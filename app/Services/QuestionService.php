<?php

namespace App\Services;

use App\Models\QuestionBank;
use Illuminate\Http\Request;
use App\Imports\QuestionsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Crypt;
use App\Interfaces\QuestioneInterface;
use App\Jobs\ImportQuestionsFromExcel;
use App\Http\Resources\successResource;

class QuestionService implements QuestioneInterface
{
    public function addFromForm($request)
    {
        $plainQuestion = $request->question;
        $questionHash = hash('sha256', $plainQuestion);

        // التحقق من عدم وجود سؤال مكرر
        if (QuestionBank::where('question_hash', $questionHash)->exists()) {
            return response()->json(['message' => 'السؤال موجود مسبقاً']);
        }


        QuestionBank::create([
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

        return new successResource('تمت إضافة السؤال بنجاح');
    }

    public function addFromExcel($request): void
    {
        $path = $request->file('file')->storeAs(
            'temp_imports',
            uniqid() . '_' . $request->file('file')->getClientOriginalName()
        );
        ImportQuestionsFromExcel::dispatch($path, $request->specialization_id);

    }
}
