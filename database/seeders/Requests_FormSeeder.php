<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\FormPath;
use App\Models\FormElement;
use Illuminate\Database\Seeder;
use App\Enums\Element_Type;
use App\Enums\FormStatus;

class Requests_FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الفورم الاول
        $form = Form::create([
            'name' => 'طلب ترشيح لامتحان داخل القطر',
            'cost' => 0,
            'status' => FormStatus::Active->value,
        ]);

        // ربط مع دائرة الامتحانات فقط
        FormPath::create([
            'form_id' => $form->id,
            'path_id' => 7,
        ]);
        $elements = [
            ['label' => 'الاسم الأول', 'type' => Element_Type::INPUT],
            ['label' => 'اسم الأب', 'type' => Element_Type::INPUT],
            ['label' => 'اسم العائلة', 'type' => Element_Type::INPUT],
            ['label' => 'الرقم الوطني', 'type' => Element_Type::NUMBER],
            ['label' => 'مكان وتاريخ الولادة', 'type' => Element_Type::INPUT],
            ['label' => 'رقم الجوال', 'type' => Element_Type::NUMBER],
            ['label' => 'رقم الهاتف الأرضي', 'type' => Element_Type::NUMBER],
            ['label' => 'عنوان السكن المعتمد', 'type' => Element_Type::INPUT],
            ['label' => 'الجنسية', 'type' => Element_Type::INPUT],
            ['label' => 'خريج جامعة', 'type' => Element_Type::INPUT],
            ['label' => 'مسجل في سجل', 'type' => Element_Type::INPUT],
            ['label' => 'برقم', 'type' => Element_Type::NUMBER],
            ['label' => 'تاريخ', 'type' => Element_Type::DATE],
            ['label' => 'حاصى على ترخيص', 'type' => Element_Type::INPUT],
            ['label' => 'رقم', 'type' => Element_Type::NUMBER],
            ['label' => 'تاريخ', 'type' => Element_Type::DATE],
            ['label' => 'بصفة', 'type' => Element_Type::INPUT],
            ['label' => 'لدى وزارة التعليم العالي_جامعة', 'type' => Element_Type::INPUT],
            ['label' => 'يرجى الموافقة على دخولي الاختبار النهائي لأختصاص', 'type' => Element_Type::INPUT],
            ['label' => 'فرعي', 'type' => Element_Type::CHECKBOX],
            ['label' => 'رئيسي', 'type' => Element_Type::CHECKBOX],
            ['label' => 'مسبوق باختصاص رئيسي هو', 'type' => Element_Type::INPUT],
            ['label' => 'لدورة شهر', 'type' => Element_Type::TEXT],
            ['label' => 'نيسان', 'type' => Element_Type::CHECKBOX],
            ['label' => 'تشرين الأول', 'type' => Element_Type::CHECKBOX],
            ['label' => 'السنة', 'type' => Element_Type::NUMBER],
            ['label' => 'المرفقات', 'type' => Element_Type::TEXT],
            ['label' => 'صورة الهوية الشخصية', 'type' => Element_Type::ATTACHED_IMAGE],
            ['label' => 'صورة للترخيص المؤقت', 'type' => Element_Type::ATTACHED_IMAGE],
            ['label' => 'بيان بالإجازات بدون أجر', 'type' => Element_Type::ATTACHED_FILE],
            ['label' => 'موافقة عميد الكلية على الترشح', 'type' => Element_Type::ATTACHED_FILE],
            ['label' => 'كشف العلامات الأصلي', 'type' => Element_Type::ATTACHED_FILE],
            ['label' => 'صورة بيان خدمة', 'type' => Element_Type::ATTACHED_IMAGE],
        ];
        foreach ($elements as $element) {
            FormElement::create([
                'form_id' => $form->id,
                'label' => $element['label'],
                'type' => $element['type'],
            ]);
        }

        // الفورم الثاني
        $form2 = Form::create([
            'name' => 'طلب ترشيح للأمتحان خارج القطر',
            'cost' => 0,
            'status' => FormStatus::Active->value,
        ]);

        FormPath::create([
            'form_id' => $form2->id,
            'path_id' => 7,
        ]);
        $elements2 = [
            ['label' => 'الاسم الأول', 'type' => Element_Type::INPUT],
            ['label' => 'اسم الأب', 'type' => Element_Type::INPUT],
            ['label' => 'اسم العائلة', 'type' => Element_Type::INPUT],
            ['label' => 'الرقم الوطني', 'type' => Element_Type::NUMBER],
            ['label' => 'مكان وتاريخ الولادة', 'type' => Element_Type::INPUT],
            ['label' => 'رقم الجوال', 'type' => Element_Type::NUMBER],
            ['label' => 'رقم الهاتف الأرضي', 'type' => Element_Type::NUMBER],
            ['label' => 'عنوان السكن المعتمد', 'type' => Element_Type::INPUT],
            ['label' => 'الجنسية', 'type' => Element_Type::INPUT],
            ['label' => 'خريج جامعة', 'type' => Element_Type::INPUT],
            ['label' => 'مسجل في سجل', 'type' => Element_Type::INPUT],
            ['label' => 'برقم', 'type' => Element_Type::NUMBER],
            ['label' => 'تاريخ', 'type' => Element_Type::DATE],
            ['label' => 'حاصى على ترخيص', 'type' => Element_Type::INPUT],
            ['label' => 'رقم', 'type' => Element_Type::NUMBER],
            ['label' => 'تاريخ', 'type' => Element_Type::DATE],
            ['label' => 'بصفة', 'type' => Element_Type::INPUT],
            ['label' => 'لدى مديرية الصحة', 'type' => Element_Type::INPUT],
            ['label' => 'يرجى الموافقة على دخولي الاختبار النهائي لأختصاص', 'type' => Element_Type::INPUT],
            ['label' => 'لدورة شهر', 'type' => Element_Type::TEXT],
            ['label' => 'نيسان', 'type' => Element_Type::CHECKBOX],
            ['label' => 'تشرين الأول', 'type' => Element_Type::CHECKBOX],
            ['label' => 'السنة', 'type' => Element_Type::NUMBER],
            ['label' => 'المرفقات', 'type' => Element_Type::TEXT],
            ['label' => 'صورة الهوية الشخصية', 'type' => Element_Type::ATTACHED_IMAGE],
            ['label' => 'صورة عن قرار لجنة التحميص', 'type' => Element_Type::ATTACHED_IMAGE]
        ];

        foreach ($elements2 as $element) {
            FormElement::create([
                'form_id' => $form2->id,
                'label' => $element['label'],
                'type' => $element['type'],
            ]);
        }
        //  الفورم الثالث
        $form3 = Form::create([
            'name' => 'طلب اعتذار عن الاختبار',
            'cost' => 0,
            'status' => FormStatus::Active->value,
        ]);

        FormPath::create([
            'form_id' => $form3->id,
            'path_id' => 7,
        ]);
        $elements3 = [
            ['label' => 'الاسم الأول', 'type' => Element_Type::INPUT],
            ['label' => 'اسم الأب', 'type' => Element_Type::INPUT],
            ['label' => 'اسم العائلة', 'type' => Element_Type::INPUT],
            ['label' => 'الرقم الوطني', 'type' => Element_Type::NUMBER],
            ['label' => 'مكان وتاريخ الولادة', 'type' => Element_Type::INPUT],
            ['label' => 'رقم الجوال', 'type' => Element_Type::NUMBER],
            ['label' => 'رقم الهاتف الأرضي', 'type' => Element_Type::NUMBER],
            ['label' => 'عنوان السكن المعتمد', 'type' => Element_Type::INPUT],
            ['label' => 'الجنسية', 'type' => Element_Type::INPUT],
            ['label' => 'خريج جامعة', 'type' => Element_Type::INPUT],
            ['label' => 'الاختصاص', 'type' => Element_Type::INPUT],
            ['label' => 'أرجو قبول اعتذاري عن دخول الاختبار', 'type' => Element_Type::TEXT],
            ['label' => 'كتابي', 'type' => Element_Type::CHECKBOX],
            ['label' => 'عملي', 'type' => Element_Type::CHECKBOX],
            ['label' => 'دورة', 'type' => Element_Type::INPUT],
            ['label' => 'السنة', 'type' => Element_Type::NUMBER],
            ['label' => 'هل يوجد اعتذارات سابقة؟', 'type' => Element_Type::TEXT],
            ['label' => 'نعم', 'type' => Element_Type::CHECKBOX],
            ['label' => 'لا يوجد', 'type' => Element_Type::CHECKBOX],
            ['label' => 'عن الاختبار', 'type' => Element_Type::TEXT],
            ['label' => 'كتابي', 'type' => Element_Type::CHECKBOX],
            ['label' => 'عملي', 'type' => Element_Type::CHECKBOX],
            ['label' => 'دورة', 'type' => Element_Type::INPUT],
            ['label' => 'السنة', 'type' => Element_Type::NUMBER],

        ];
        foreach ($elements3 as $element) {
            FormElement::create([
                'form_id' => $form3->id,
                'label' => $element['label'],
                'type' => $element['type'],
            ]);
        }
    }
}
