<?php

namespace App\Services;

use App\Models\FormContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;

class FormContentService
{
    public function createFormContent(array $data)
    {
        return DB::transaction(function () use ($data) {
            $doctor = Auth::user()->doctor;

            $formContent = FormContent::create([
                'form_id' => $data['form_id'],
                'doctor_id' => $doctor->id,
            ]);

            $this->storeElementValues($formContent, $data['elements'] ?? []);
            $this->storeMedia($formContent, $data['media'] ?? []);

            return $formContent;
        });
    }

    protected function storeElementValues(FormContent $formContent, array $elements): void
    {
        foreach ($elements as $label => $value) {
            $formElement = $formContent->form->elements->firstWhere('label', $label);
            if ($formElement) {
                $formContent->elementValues()->create([
                    'form_element_id' => $formElement->id,
                    'value' => $value,
                ]);
            }
        }
    }

    protected function storeMedia(FormContent $formContent, array $media): void
    {
        $receiptPath = $this->storeFileIfExists($media['receipt'] ?? null, 'receipts');

        $files = $this->extractFiles($media, 'file');
        $images = $this->extractFiles($media, 'image');

        $max = max(count($files), count($images));
        if ($receiptPath && $max === 0) {
            $formContent->media()->create([
                'receipt' => $receiptPath,
                'file' => null,
                'image' => null,
            ]);
        }
        for ($i = 0; $i < $max; $i++) {
            $formContent->media()->create([
                'receipt' => $receiptPath,
                'file' => $files[$i] ?? null,
                'image' => $images[$i] ?? null,
            ]);
        }
    }

    protected function storeFileIfExists($file, $folder): ?string
    {
        return $file instanceof UploadedFile ? $file->store($folder, 'public') : null;
    }

    protected function extractFiles(array $media, string $key): array
    {
        $result = [];
        foreach ($media as $label => $item) {
            if ($label === 'receipt') continue;
            if (is_array($item) && isset($item[$key]) && $item[$key] instanceof UploadedFile) {
                $result[] = $item[$key]->store("{$key}s", 'public');
            }
        }
        return $result;
    }
}

