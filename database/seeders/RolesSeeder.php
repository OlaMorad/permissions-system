<?php

namespace Database\Seeders;

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
        $roles = [
            'admin',
            'sub_admin',
            'Head of Front Desk',
            'Front Desk User',
            'Head of Finance Officer',
            'Finance Officer',
            'Head of Academic Committee',
            'Academic Committee',
            'Head of Certificate Officer',
            'Certificate Officer',
            'Head of Exam Officer',
            'Exam Officer',
            'Head of Residency Officer',
            'Residency Officer',
            'Head of Selection & Admission Officer',
            'Selection & Admission Officer',
            'Doctor'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
