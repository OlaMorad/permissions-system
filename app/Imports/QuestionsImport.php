<?php

namespace App\Imports;

use App\Models\QuestionBank;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Concerns\ToCollection;

class QuestionsImport implements ToCollection
{
    protected int $specializationId;

    public function __construct(int $specializationId)
    {
        $this->specializationId = $specializationId;
    }
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new QuestionBank([
            //
        ]);
    }

    public function collection(Collection $rows)
    {
        $imported = 0;  // عداد لعدد الأسطر المستوردة

        foreach ($rows as $index => $row) {
            // تجاهل أول صف إذا كان عنوان الأعمدة
            if ($index === 0 && $row[0] === 'question') {
                continue;
            }

            // التحقق من أن السطر يحتوي على 7 أعمدة على الأقل
            if (count($row) < 7 || in_array(null, [$row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6]])) {
                Log::warning('سطر غير صالح في ملف الاستيراد', [
                    'row_index' => $index,
                    'data' => $row->toArray(),
                ]);
                continue;
            }

            $plainQuestion = $row[0];
            $questionHash = hash('sha256', $plainQuestion);

            // التحقق من التكرار قبل الحفظ
            if (QuestionBank::where('question_hash', $questionHash)->exists()) {
                Log::info('سؤال مكرر تم تجاهله', [
                    'row_index' => $index,
                    'question' => $plainQuestion,
                ]);
                continue;
            }

            // إنشاء السؤال
            try {
                QuestionBank::create([
                    'question'          => Crypt::encryptString($row[0]),
                    'specialization_id' => $this->specializationId,
                    'option_a'          => Crypt::encryptString($row[1]),
                    'option_b'          => Crypt::encryptString($row[2]),
                    'option_c'          => Crypt::encryptString($row[3]),
                    'option_d'          => Crypt::encryptString($row[4]),
                    'correct_answer'    => Crypt::encryptString($row[5]),
                    'difficulty_level'  => $row[6],
                    'question_hash'     => $questionHash,
                ]);
                $imported++;
            } catch (\Throwable $e) {
                Log::error('فشل في استيراد سطر', [
                    'row_index' => $index,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('تم استيراد الأسئلة من ملف Excel', [
            'specialization_id' => $this->specializationId,
            'total_rows' => count($rows),
            'imported_successfully' => $imported,
            'failed_rows' => count($rows) - 1 - $imported, // -1 بسبب رؤوس الأعمدة
        ]);
    }
}
