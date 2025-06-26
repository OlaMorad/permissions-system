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
            'المدير' => 'المدير',
            'نائب المدير' => 'نائب المدير',
            'رئيس الديوان' => 'الديوان',
            'موظف الديوان' => 'الديوان',
            'رئيس المالية' => 'المالية',
            'موظف المالية' => 'المالية',
            'رئيس مجالس علمية' => 'المجالس العلمية',
            'موظف مجالس علمية' => 'المجالس العلمية',
            'رئيس الشهادات' => 'الشهادات',
            'موظف الشهادات' => 'الشهادات',
            'رئيس الامتحانات' => 'الامتحانات',
            'موظف الامتحانات' => 'الامتحانات',
            'رئيس الإقامة' => 'الإقامة',
            'موظف الإقامة' => 'الإقامة',
            'رئيس المفاضلة' => 'المفاضلة',
            'موظف المفاضلة' =>  'المفاضلة',
            'طبيب' => null, // الطبيب مو من ضمن المسار تبع المعاملة
        ];

        foreach ($rolesWithPaths as $roleName => $pathName) {
            $pathId = null;

            if (!is_null($pathName)) {
                $path = Path::firstOrCreate(['name' => $pathName]);
                $pathId = $path->id;
            }

            Role::updateOrCreate(
                ['name' => $roleName],
                ['path_id' => $pathId]
            );
        }
}
}
