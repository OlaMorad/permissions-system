<?php

namespace Database\Seeders;

use App\Models\Doctor;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        //  Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin@123'),
                'phone'=>'0956883161',
                'address'=>'دمشق',
                'avatar'=>'...'
            ]
        );

        $admin->assignRole('المدير');

        //  Sub Admin
        $subAdmin = User::firstOrCreate(
            ['email' => 'subadmin@gmail.com'],
            [
                'name' => 'Sub Admin',
                'password' => Hash::make('sub_admin'),
                 'phone'=>'0950883161',
                'address'=>'دمشق',
                'avatar'=>'...'
            ]
        );

        $subAdmin->assignRole('نائب المدير');

        $doctor = User::firstOrCreate([
            'name' => 'heba',
            'email' => 'heba@gmail.com',
            'password' => Hash::make('heba2007'),
             'phone'=>'0956823161',
                'address'=>'دمشق',
                'avatar'=>'...'
        ]);
        $doctor->assignRole('الطبيب');
        Doctor::create([
            'user_id' =>  $doctor->id,
        ]);
    }
}
