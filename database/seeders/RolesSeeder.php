<?php

namespace Database\Seeders;

use App\Models\Path;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rolesWithPaths = [
            'admin' => 'admin',
            'sub_admin' => 'Sub_admin',
            'Head of Front Desk' => 'Front Desk',
            'Front Desk User' => 'Front Desk',
            'Head of Finance Officer' => 'Finance',
            'Finance Officer' => 'Finance',
            'Head of Academic Committee' => 'Academic Committee',
            'Academic Committee' => 'Academic Committee',
            'Head of Certificate Officer' => 'Certificate',
            'Certificate Officer' => 'Certificate',
            'Head of Exam Officer' => 'Exam',
            'Exam Officer' => 'Exam',
            'Head of Residency Officer' => 'Residency',
            'Residency Officer' => 'Residency',
            'Head of Selection & Admission Officer' => 'Selection & Admission',
            'Selection & Admission Officer' => 'Selection & Admission',
            'Doctor' => 'Doctor',
        ];

        foreach ($rolesWithPaths as $roleName => $pathName) {
            $path = Path::firstOrCreate(['name' => $pathName]);

            Role::updateOrCreate(
                ['name' => $roleName],
                ['path_id' => $path->id]
            );
    }
}
}
