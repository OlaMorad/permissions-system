<?php

 namespace App\Services;

 use App\Interfaces\FormInputInterface;

class ManualFormInputService implements FormInputInterface
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function extractElements(): array
    {
        $elements = [];

        foreach ($this->data['elements'] as $item) {
            if (!isset($item['label']) || !isset($item['type'])) {
                continue;
            }

            $elements[] = [
                'label' => trim($item['label']),
                'type' => (int) $item['type'],
            ];
        }

        return $elements;
    }

    public function getPathIds(): array
    {
        return $this->data['path_ids'] ?? [];
    }

    public function getName(): string
    {
        return $this->data['name'];
    }
}
