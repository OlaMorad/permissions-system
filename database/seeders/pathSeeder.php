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
            'Ministry',
            'admin',
            'Sub_admin',
            'Front Desk',
            'Finance',
            'Academic Committee',
            'Certificate',
            'Exam',
            'Residency',
            'Selection & Admission',
            'Doctor'
        ];
        foreach ($paths as $path) {
            Path::firstOrCreate(['name' => $path]);
        }
    }
}
