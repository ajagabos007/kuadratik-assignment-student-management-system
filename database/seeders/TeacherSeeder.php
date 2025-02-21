<?php

namespace Database\Seeders;

use App\Models\Teacher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teacher = Teacher::factory(30)->make()->toArray();

        $start_time = now();
        Teacher::upsert(
            $teacher,
            uniqueBy: ['registration_no'],
            update: ['registration_no']
        );

        $end_time = now();

        $teachers = Teacher::whereBetween('created_at', [
            $start_time->toDateTimeString(),  $end_time->toDateTimeString()
        ])
        ->lazy();

        foreach($teachers  as $teacher)
        {
            if($teacher->created_at == $teacher->updated_at)
                Event::dispatch('eloquent.created: ' . $teacher::class, $teacher);
            else
                Event::dispatch('eloquent.updated: ' . $teacher::class, $teacher);
        }   
    }
}
