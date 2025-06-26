<?php

namespace App\Services;

use App\Enums\Element_Type;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\Image;
use PhpOffice\PhpWord\Element\TextRun;
use App\Interfaces\FormInputInterface;

class WordFormInputService implements FormInputInterface
{
    protected string $filePath;
    protected string $fileTitle;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $this->fileTitle = $this->extractFileTitle($filePath);
    }

    private function extractFileTitle(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME); // اسم الملف بدون الامتداد
    }

    public function extractElements(): array
    {
        $phpWord = IOFactory::load($this->filePath);
        $elements = [];

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {

                // صور مرفقة
                if ($element instanceof Image) {
                    $elements[] = [
                        'label' => 'صورة مرفقة',
                        'type'  => Element_Type::ATTACHED_IMAGE->value,
                    ];
                    continue;
                }

                // TextRun متعدد الأسطر
                if ($element instanceof TextRun) {
                    $lines = [];

                    foreach ($element->getElements() as $subElement) {
                        if (method_exists($subElement, 'getText')) {
                            $text = trim($subElement->getText());
                            if ($this->isUsefulText($text)) {
                                $lines[] = $text;
                            }
                        }
                    }

                    if (count($lines) > 0) {
                        $joined = implode(' ', $lines);
                        $elements[] = [
                            'label' => $joined,
                            'type' => $this->detectTypeFromLine($joined)->value,
                        ];
                    }
                    continue;
                }

                // نص عادي
                if (method_exists($element, 'getText')) {
                    $line = trim($element->getText());
                    if ($this->isUsefulText($line)) {
                        $elements[] = [
                            'label' => $line,
                            'type'  => $this->detectTypeFromLine($line)->value,
                        ];
                    }
                }
            }
        }

        return $elements;
    }

    private function isUsefulText(string $text): bool
    {
        $text = trim($text);

        $useless = ['Label:', ':', '-', '(', ')', '[', ']', 'PDF)', '...', '.......................'];

        if (in_array($text, $useless, true)) {
            return false;
        }

        if (preg_match('/^[\.\-\:\[\]\(\)]+$/u', $text)) {
            return false;
        }

        return $text !== '';
    }

    private function detectTypeFromLine(string $line): Element_Type
    {
        $line = trim($line);

        // 0. إذا السطر يحتوي على اسم ملف الوورد (غالبا عنوان النموذج) => TITLE
        if (mb_stripos($line, $this->fileTitle) !== false) {
            return Element_Type::TITLE;
        }

        // 1. TYPE: TITLE (نموذج، بيان، Form Name، نموذج طلب...)
        if (preg_match('/^(بيان|نموذج|Form\s?Name|.*نموذج طلب)/iu', $line)) {
            return Element_Type::TITLE;
        }

        // 2. TYPE: CHECKBOX (رموز أو كلمات اختيار واضحة)
        if (
            preg_match('/[☐✓■]/u', $line)
            || preg_match('/\b(عربية|أجنبية|ذكر|أنثى|رئيسي|فر عي|عام|تخصصي|جامعة|سنة|دورة|اختبار|عام|تخصصي|جامعة)\b/u', $line)
        ) {
            return Element_Type::CHECKBOX;
        }

        // 3. TYPE: Multiple Choice (اختيار من قائمة)
        if (preg_match('/^(اختر|اختر من القائمة|الخيارات)\s*:?/iu', $line)) {
            return Element_Type::Multiple_Choice;
        }

        // 4. TYPE: DATE (كلمات دالة على التاريخ)
        if (preg_match('/\b(تاريخ الميلاد|تاريخ|date|اختبار النهائي|التاريخ)\b/iu', $line)) {
            return Element_Type::DATE;
        }

        // 5. TYPE: NUMBER (كلمات دالة على أرقام وهواتف ورقم وطني...)
        if (preg_match('/\b(رقم|الهاتف|الهويه|الرقم الوطني|رقم الهوية|رقم الهاتف|phone|number|عدد السنوات)\b/iu', $line)) {
            return Element_Type::NUMBER;
        }

        // 6. TYPE: ATTACHED_FILE (طلبات رفع ملفات ومستندات وشهادات)
        $fileKeywords = ['أرفق', 'إرفاق', 'مطلوب رفع ملف', 'ارفاق مستند', 'رفع ملف', 'رفع مستند', 'ارفاق وثيقة', 'نسخة من شهادة'];
        foreach ($fileKeywords as $keyword) {
            if (mb_stripos($line, $keyword) !== false) {
                return Element_Type::ATTACHED_FILE;
            }
        }

        // 7. TYPE: ATTACHED_IMAGE (طلبات رفع صور، كلمات دالة على ختم أو مختومة)
        if (
            preg_match('/(أرفق|إرفاق).*(صورة|صوره|image|jpeg|png|مختومة)/iu', $line)
            || preg_match('/(صورة مختومة|ختم|مشفى|مختومة)/iu', $line)
        ) {
            return Element_Type::ATTACHED_IMAGE;
        }

        // 8. TYPE: INPUT (حقول نصية قابلة للتعبئة)
        $inputKeywords = [
            'الاسم الثلاثي',
            'الاسم الثاني',
            'الاسم الثالث',
            'القسم الأول',
            'القسم الثاني',
            'عنوان السكن',
            'ملاحظات',
            'عدد السنوات المطلوبة',
            'بيان برنامج تدريبي',
            'عنوان',
            'المدينة',
            'الدولة',
            'البريد الإلكتروني',
            'اسم المستخدم',
            'كلمة المرور',
            'الاسم الكامل',
            'الاسم',
            'ملاحظات إضافية',
        ];
        foreach ($inputKeywords as $keyword) {
            if (mb_stripos($line, $keyword) !== false) {
                return Element_Type::INPUT;
            }
        }

        // 9. الافتراضي: TEXT (نص عادي)
        return Element_Type::TEXT;
    }
}

