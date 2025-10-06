<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\TodaysArrivalBranch;

class TodaysArrivalBranchSeeder extends Seeder
{
    public function run()
    {
        $branches = [
            [
                'name' => 'Main Branch - Al Qusais',
                'location' => 'Al Qusais',
                'whatsapp_number' => '+971501234567',
                'contact_person' => 'Ahmed',
                'address' => 'Al Qusais Industrial Area, Dubai, UAE',
                'is_active' => true,
            ],
            [
                'name' => 'Sharjah Branch',
                'location' => 'Sharjah',
                'whatsapp_number' => '+971507654321',
                'contact_person' => 'Mohammed',
                'address' => 'Sharjah Industrial Area, Sharjah, UAE',
                'is_active' => true,
            ],
            [
                'name' => 'Abu Dhabi Branch',
                'location' => 'Abu Dhabi',
                'whatsapp_number' => '+971509876543',
                'contact_person' => 'Hassan',
                'address' => 'Abu Dhabi Industrial Area, Abu Dhabi, UAE',
                'is_active' => true,
            ],
            [
                'name' => 'Ajman Branch',
                'location' => 'Ajman',
                'whatsapp_number' => '+971505432109',
                'contact_person' => 'Omar',
                'address' => 'Ajman Industrial Area, Ajman, UAE',
                'is_active' => true,
            ],
        ];

        foreach ($branches as $branch) {
            TodaysArrivalBranch::create($branch);
        }
    }
}