<?php

namespace App\Services;

use App\Enums\Element_Type;
use PhpOffice\PhpWord\IOFactory;
use App\Interfaces\FormInputInterface;

class WordFormInputService implements FormInputInterface
{
    protected string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function extractElements(): array
    {
        $phpWord = IOFactory::load($this->filePath);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                }
            }
        }

        $lines = explode("\n", $text);
        $elements = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // تحليل نوع العنصر بناءً على محتوى السطر
            $type = $this->detectTypeFromLine($line);

            $elements[] = [
                'label' => $line,
                'type'  => $type->value, // نرجّع القيمة الرقمية (int) من الـ enum
            ];
        }

        return $elements;
    }



    private function detectTypeFromLine(string $line): Element_Type
    {
        if (preg_match('/[☐✓]/u', $line) || preg_match('/\b(العربية|الأجنبية)\b/u', $line)) {
            return Element_Type::CHECKBOX;
        }

        if (preg_match('/:.*[\.]{3,}/u', $line) || preg_match('/:.*\s{3,}/u', $line)) {
            return Element_Type::INPUT;
        }

        if (strpos($line, ':') !== false) {
            return Element_Type::INPUT;
        }

        return Element_Type::TEXT;
    }
}
