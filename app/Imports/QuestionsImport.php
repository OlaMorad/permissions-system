<?php

namespace App\Imports;

use App\Models\QuestionBank;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Services\FirebaseNotificationService;

class QuestionsImport implements ToCollection
{
    protected int $specializationId;
    protected string $batchId;

    public function __construct(int $specializationId, string $batchId)
    {
        $this->specializationId = $specializationId;
        $this->batchId = $batchId;
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
    $imported = 0;  // عداد الأسطر المستوردة
    $failedRows = []; // لتجميع الأسطر الفاشلة

    $firebase = app(FirebaseNotificationService::class);

    foreach ($rows as $index => $row) {
        // تجاهل رأس الأعمدة
        if ($index === 0 && $row[0] === 'question') continue;

        if (count($row) < 7 || in_array(null, [$row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6]])) {
            $failedRows[] = ['row_index' => $index, 'data' => $row->toArray(), 'reason' => 'سطر غير صالح'];

            // إرسال إشعار لرئيس الامتحانات لكل سطر فشل
            $firebase->sendToRole(
                'رئيس الامتحانات',
                'فشل استيراد سؤال',
                "سطر رقم {$index} لم يتم استيراده: بيانات ناقصة أو غير صالحة",
                ['row_index' => $index]
            );
            continue;
        }

        $plainQuestion = $row[0];
        $questionHash = hash('sha256', $plainQuestion);

        if (QuestionBank::where('question_hash', $questionHash)->exists()) {
            $failedRows[] = ['row_index' => $index, 'data' => $row->toArray(), 'reason' => 'سؤال مكرر'];
            $firebase->sendToRole(
                'رئيس الامتحانات',
                'سؤال مكرر تم تجاهله',
                "سطر رقم {$index} لم يتم استيراده لأنه مكرر",
                ['row_index' => $index]
            );
            continue;
        }

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
                'batch_id'          => $this->batchId,
            ]);
            $imported++;
        } catch (\Throwable $e) {
            $failedRows[] = ['row_index' => $index, 'data' => $row->toArray(), 'reason' => $e->getMessage()];

            $firebase->sendToRole(
                'رئيس الامتحانات',
                'فشل استيراد سؤال',
                "سطر رقم {$index} لم يتم استيراده بسبب خطأ: {$e->getMessage()}",
                ['row_index' => $index]
            );
        }
    }

    // بعد انتهاء الاستيراد، إرسال إشعار للمدير
    $firebase->sendToRole(
        'المدير',
        'انتهاء استيراد ملف أسئلة',
        "تم استيراد ملف أسئلة بنجاح. Batch ID: {$this->batchId}, عدد الأسئلة المستوردة: {$imported}, عدد الأسطر الفاشلة: " . count($failedRows),
        ['batch_id' => $this->batchId, 'imported' => $imported, 'failed' => count($failedRows)]
    );

    Log::info('تم استيراد الأسئلة من ملف Excel', [
        'specialization_id' => $this->specializationId,
        'total_rows' => count($rows),
        'imported_successfully' => $imported,
        'failed_rows' => $failedRows,
    ]);
}

}
