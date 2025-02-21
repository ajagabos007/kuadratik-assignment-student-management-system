<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Event;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::factory(30)->make()->toArray();

        $start_time = now();
        Student::upsert(
            $students,
            uniqueBy: ['registration_no'],
            update: ['registration_no']
        );

        $end_time = now();

        $students = Student::whereBetween('created_at', [
            $start_time->toDateTimeString(),  $end_time->toDateTimeString()
        ])
        ->lazy();

        foreach($students  as $student)
        {
            if($student->created_at == $student->updated_at)
                Event::dispatch('eloquent.created: ' . $student::class, $student);
            else
                Event::dispatch('eloquent.updated: ' . $student::class, $student);
        }   
    }
}
