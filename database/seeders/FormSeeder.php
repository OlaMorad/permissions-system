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
                ->count(10)
                ->create()
                ->each(function ($form) {

                    // حدد معرف المسار الثابت
                    $firstPathId = 4;

                    // جلب باقي المعرفات عدا 4
                    $allPathIds = \App\Models\Path::where('id', '!=', $firstPathId)->pluck('id')->toArray();
                    shuffle($allPathIds);

                    // اختار عدد عشوائي من المسارات وأضف 4 كبداية
                    $numberOfPaths = rand(3, 5);
                    $selectedPathIds = array_merge([$firstPathId], array_slice($allPathIds, 0, $numberOfPaths - 1));

                    // اربط المسارات مع الفورم
                    foreach ($selectedPathIds as $pathId) {
                        FormPath::create([
                            'form_id' => $form->id,
                            'path_id' => $pathId,
                        ]);
                    }

                    // إنشاء عناصر للفورم
                    $elements = FormElement::factory()->count(3)->make();
                    $form->elements()->saveMany($elements);

                    // إنشاء المحتوى
                    $content = FormContent::factory()->create([
                        'form_id' => $form->id,
                        'doctor_id' => 1, // أو يمكن توليد عشوائي لو فيه علاقة
                    ]);

                    // إنشاء وسائط
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
                });
        });
    }
}
