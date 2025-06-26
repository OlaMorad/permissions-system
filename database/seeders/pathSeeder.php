<?php

namespace Database\Seeders;

use App\Models\Path;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class pathSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paths = [
            'الوزارة',
            'المدير',
            'نائب المدير',
            'الديوان',
            'المالية',
            'المجالس العلمية',
            'الشهادات',
            'الامتحانات',
            'الإقامة',
            'المفاضلة',
        ];
        foreach ($paths as $path) {
            Path::firstOrCreate(['name' => $path]);
        }
    }
}
