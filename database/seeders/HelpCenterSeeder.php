<?php

namespace Database\Seeders;

use App\Models\HelpCenter;
use App\Models\Hotline;
use Illuminate\Database\Seeder;

class HelpCenterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. National Crisis Helpline (Hotline Only)
        $center1 = HelpCenter::create([
            'name' => 'National Crisis Helpline',
            'type' => 'hotline_only',
            'address' => null,
            'city' => null,
            'state' => null,
            'zip_code' => null,
            'latitude' => null,
            'longitude' => null,
            'working_hours' => json_encode(['monday' => '24/7', 'tuesday' => '24/7', 'wednesday' => '24/7', 'thursday' => '24/7', 'friday' => '24/7', 'saturday' => '24/7', 'sunday' => '24/7']),
            'is_active' => true,
        ]);

        $center1->hotlines()->create([
            'name' => 'National Crisis Line',
            'phone_number' => '999',
            'is_toll_free' => true,
            'description' => '24/7 national crisis support for mental health emergencies',
            'operating_hours' => json_encode(['monday' => '24/7', 'tuesday' => '24/7', 'wednesday' => '24/7', 'thursday' => '24/7', 'friday' => '24/7', 'saturday' => '24/7', 'sunday' => '24/7']),
            'is_active' => true,
        ]);

        // 2. Dhaka Crisis Support Center
        $center2 = HelpCenter::create([
            'name' => 'Dhaka Crisis Support Center',
            'type' => 'crisis_center',
            'address' => '123 Mirpur Road, Dhaka',
            'city' => 'Dhaka',
            'state' => 'Dhaka',
            'zip_code' => '1216',
            'latitude' => 23.8103,
            'longitude' => 90.4125,
            'working_hours' => json_encode(['monday' => '9AM-5PM', 'tuesday' => '9AM-5PM', 'wednesday' => '9AM-5PM', 'thursday' => '9AM-5PM', 'friday' => '9AM-1PM', 'saturday' => 'Closed', 'sunday' => 'Closed']),
            'is_active' => true,
        ]);

        $center2->hotlines()->create([
            'name' => 'Dhaka Crisis Line',
            'phone_number' => '+880-2-9123456',
            'is_toll_free' => false,
            'description' => 'Local crisis support for Dhaka residents',
            'operating_hours' => json_encode(['monday' => '9AM-5PM', 'tuesday' => '9AM-5PM', 'wednesday' => '9AM-5PM', 'thursday' => '9AM-5PM', 'friday' => '9AM-1PM', 'saturday' => 'Closed', 'sunday' => 'Closed']),
            'is_active' => true,
        ]);

        // 3. Central Police Station
        $center3 = HelpCenter::create([
            'name' => 'Central Police Station',
            'type' => 'police_station',
            'address' => '456 Shahbagh, Dhaka',
            'city' => 'Dhaka',
            'state' => 'Dhaka',
            'zip_code' => '1217',
            'latitude' => 23.7381,
            'longitude' => 90.3954,
            'working_hours' => json_encode(['monday' => '24/7', 'tuesday' => '24/7', 'wednesday' => '24/7', 'thursday' => '24/7', 'friday' => '24/7', 'saturday' => '24/7', 'sunday' => '24/7']),
            'is_active' => true,
        ]);

        $center3->hotlines()->create([
            'name' => 'Police Emergency',
            'phone_number' => '100',
            'is_toll_free' => true,
            'description' => 'Emergency police response',
            'operating_hours' => json_encode(['monday' => '24/7', 'tuesday' => '24/7', 'wednesday' => '24/7', 'thursday' => '24/7', 'friday' => '24/7', 'saturday' => '24/7', 'sunday' => '24/7']),
            'is_active' => true,
        ]);

        // 4. Dhaka Medical College Hospital
        $center4 = HelpCenter::create([
            'name' => 'Dhaka Medical College Hospital',
            'type' => 'hospital',
            'address' => '789 Shahbagh, Dhaka',
            'city' => 'Dhaka',
            'state' => 'Dhaka',
            'zip_code' => '1217',
            'latitude' => 23.7381,
            'longitude' => 90.3954,
            'working_hours' => json_encode(['monday' => '24/7', 'tuesday' => '24/7', 'wednesday' => '24/7', 'thursday' => '24/7', 'friday' => '24/7', 'saturday' => '24/7', 'sunday' => '24/7']),
            'is_active' => true,
        ]);

        $center4->hotlines()->create([
            'name' => 'Emergency Room',
            'phone_number' => '10666',
            'is_toll_free' => true,
            'description' => '24/7 emergency medical services',
            'operating_hours' => json_encode(['monday' => '24/7', 'tuesday' => '24/7', 'wednesday' => '24/7', 'thursday' => '24/7', 'friday' => '24/7', 'saturday' => '24/7', 'sunday' => '24/7']),
            'is_active' => true,
        ]);
    }
}
