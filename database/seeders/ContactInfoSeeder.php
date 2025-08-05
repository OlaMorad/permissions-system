<?php

namespace Database\Seeders;

use App\Models\ContactInfo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ContactInfo::create([
            'phone' => '+963987654321',
            'email' => 'doctor@example.com',
            'telegram_link' => 'https://t.me/doctor_channel',
            'whatsapp_number' => '+963987654321',
            'facebook_link' => 'https://facebook.com/doctor.profile',
        ]);
    }
}
