<?php
namespace App\Services;

use App\Models\QuestionBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Interfaces\QuestioneInterface;
use App\Jobs\ImportQuestionsFromExcel;

class QuestionService implements QuestioneInterface
{
    public function addFromForm(Request $request): void
    {
        QuestionBank::create([
            'question'          =>  Crypt::encryptString($request->question),
            'specialization_id' => $request->specialization_id,
            'option_a'          => Crypt::encryptString( $request->option_a),
            'option_b'          =>  Crypt::encryptString($request->option_b),
            'option_c'          =>  Crypt::encryptString($request->option_c),
            'option_d'          =>  Crypt::encryptString($request->option_d),
            'correct_answer'    => Crypt::encryptString( $request->correct_answer),
            'difficulty_level'  => $request->difficulty_level,
        ]);
    }

    public function addFromExcel( $request): void
    {
           $path = $request->file('file')->storeAs(
        'temp_imports',
        uniqid() . '_' . $request->file('file')->getClientOriginalName()
    );
 ImportQuestionsFromExcel::dispatch($path, $request->specialization_id);

   }
}
