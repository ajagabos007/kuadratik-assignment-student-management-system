<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attendances = Attendance::factory(30)->make()->toArray();

        Attendance::upsert(
            $attendances,
            uniqueBy: ['user_id', 'time_in'],
            update: ['user_id', 'time_in']
        );
    }
}
