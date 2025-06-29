<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Manager;
use App\Models\Employee;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('ar_SA');

        // 1. Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin@123'),
                'phone' => '0956883161',
                'address' => 'دمشق',
                'avatar' => '...',
            ]
        );
        $admin->assignRole('المدير');

        // 2. Sub Admin
        $subAdmin = User::firstOrCreate(
            ['email' => 'subadmin@gmail.com'],
            [
                'name' => 'Sub Admin',
                'password' => Hash::make('sub_admin'),
                'phone' => '0950883161',
                'address' => 'دمشق',
                'avatar' => '...',
            ]
        );
        $subAdmin->assignRole('نائب المدير');

        // 3. Doctor
        $doctor = User::firstOrCreate(
            ['email' => 'heba@gmail.com'],
            [
                'name' => 'heba',
                'password' => Hash::make('heba2007'),
                'phone' => '0956823161',
                'address' => 'دمشق',
                'avatar' => '...',
            ]
        );
        $doctor->assignRole('الطبيب');

        Doctor::firstOrCreate([
            'user_id' => $doctor->id,
        ]);

        /**
         * 4. باقي الرتب
         */
        $rolesWithPaths = [
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
            'موظف المفاضلة' => 'المفاضلة',
        ];

        $managersByDepartment = [];

        foreach ($rolesWithPaths as $roleName => $department) {
            $email = Str::slug($roleName, '_') . '@gmail.com';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $roleName,
                    'password' => Hash::make('33333333'),
                    'phone' => '09' . $faker->numberBetween(500000000, 599999999),
                    'address' => $faker->city,
                    'avatar' => 'default.png',
                ]
            );

            // assignRole لضمان إدخال model_type بشكل صحيح
            $user->assignRole($roleName);

            $role = Role::where('name', $roleName)->firstOrFail();

            if (Str::startsWith($roleName, 'رئيس')) {
                $manager = Manager::firstOrCreate([
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                ]);

                $managersByDepartment[$department] = [
                    'manager_id' => $manager->id,
                    'role_id' => $role->id,
                ];
            }

            if (Str::startsWith($roleName, 'موظف')) {
                $managerInfo = $managersByDepartment[$department] ?? null;

                if ($managerInfo) {
                    Employee::firstOrCreate([
                        'user_id' => $user->id,
                        'role_id' => $role->id,
                        'manager_id' => $managerInfo['manager_id'],
                    ]);
                }
            }
        }
    }
}
