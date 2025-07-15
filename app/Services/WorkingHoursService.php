<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use App\Models\WorkingHour;

class WorkingHoursService
{
    public function update(array $data): WorkingHour
    {
        $existing = WorkingHour::first();
        // اذا بدو يعدل وقت البداية او النعاية و دخلهن مناخد يلي دخلهن و الا منضل على القيم يلي عنا ياهن
        $data = array_merge([
            'start_time' => $existing->start_time ?? '08:00',
            'end_time' => $existing->end_time ?? '15:00',
        ], $data);
        // اذا ما دخل اي يوم عطلة افتراضيا بيكون الجمعة واذا دخل بيكون الجمعة + اليوم يلي دخلو
        if (isset($data['day_off'])) {
            $data['day_off'] = collect($data['day_off'])
                ->push('الجمعة')
                ->unique()
                ->implode(',');
        } else {
            $data['day_off'] = $existing?->day_off ?? 'الجمعة';
        }

        return WorkingHour::updateOrCreate(['id' => 1], $data);
    }

    public function get(): array
    {
        $workingHours = WorkingHour::first();

        $allDays = ['السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'];

        if (!$workingHours) {
            return [
                'start_time' => '08:00',
                'end_time' => '15:00',
                'day_off' => ['الجمعة'],
                'working_days' => array_values(array_diff($allDays, ['الجمعة'])),
            ];
        }
        // مشان تنعرض ايام العطلة بمصفوفة و بينهن فاصلة
        $dayOff = explode(',', $workingHours->day_off);
        $workingDays = array_values(array_diff($allDays, $dayOff));

        return [
            'start_time' => Carbon::createFromFormat('H:i:s', $workingHours->start_time)->format('H:i'),
            'end_time' => Carbon::createFromFormat('H:i:s', $workingHours->end_time)->format('H:i'),
            'day_off' => $dayOff,
            'working_days' => $workingDays,
        ];
    }
}
