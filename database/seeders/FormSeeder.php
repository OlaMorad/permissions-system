<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\FormPath;
use App\Models\FormMedia;
use App\Models\FormContent;
use App\Models\FormElement;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use App\Models\form_element_value;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
 // database/seeders/FormSeeder.php



public function run(): void
{
    DB::transaction(function () {
        Form::factory()
            ->count(5)
            ->create()
            ->each(function ($form) {

                // الحصول على كل معرفات المسارات مع استثناء 4 لأنه سيكون الأول دائمًا
                $allPathIds = \App\Models\Path::pluck('id')->toArray();
                $allPathIds = array_filter($allPathIds, fn($id) => $id != 4);
                shuffle($allPathIds);

                // تحديد عدد المسارات (3 إلى 5)، مع وضع 4 كأول عنصر
                $numberOfPaths = rand(3, 5);
                $selectedPathIds = array_merge([4], array_slice($allPathIds, 0, $numberOfPaths - 1));

                // ربط المسارات بالفورم حسب الترتيب
                foreach ($selectedPathIds as $pathId) {
                    FormPath::create([
                        'form_id' => $form->id,
                        'path_id' => $pathId,
                    ]);
                }

                // إنشاء عناصر الفورم
                $elements = FormElement::factory()->count(3)->make();
                $form->elements()->saveMany($elements);

                // إنشاء محتوى الفورم
                $content = FormContent::factory()->create([
                    'form_id' => $form->id,
                    // 'doctor_id' => ...
                ]);

                // إنشاء وسائط المحتوى
                FormMedia::factory()->count(2)->create([
                    'form_content_id' => $content->id,
                ]);

                // إنشاء قيم العناصر
                foreach ($elements as $element) {
                    form_element_value::factory()->create([
                        'form_content_id' => $content->id,
                        'form_element_id' => $element->id,
                    ]);
                }

                // إنشاء المعاملات بناءً على المسارات بالترتيب
                foreach ($selectedPathIds as $index => $pathId) {
                    Transaction::factory()->create([
                        'form_content_id' => $content->id,
                        'from' => $index === 0 ? null : $selectedPathIds[$index - 1],
                        'to' => $pathId,
                    ]);
                }
            });
    });
}




}
