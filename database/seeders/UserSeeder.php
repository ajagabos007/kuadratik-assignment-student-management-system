<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $test_user = User::factory()->unverified()->make([
            'first_name' => 'User',
            'last_name' => 'Test',
            'email' => 'user@example.test'
        ]);

        $users [] =  $test_user->makeVisible(['password'])
        ->makeHidden(['profile_photo_url'])
        ->toArray();

        $test_teacher = User::factory()->unverified()->make([
            'first_name' => 'Teacher',
            'last_name' => 'Test',
            'email' => 'teacher@example.test'
        ]);
        $users [] =  $test_teacher->makeVisible(['password'])
        ->makeHidden(['profile_photo_url'])
        ->toArray();

        $test_student = User::factory()->unverified()->make([
            'first_name' => 'Student',
            'last_name' => 'Test',
            'email' => 'student@example.test'
        ]);
        $users [] =  $test_student->makeVisible(['password'])
        ->makeHidden(['profile_photo_url'])
        ->toArray();

        
        $test_admin = User::factory()->unverified()->make([
            'first_name' => 'Admin',
            'last_name' => 'Test',
            'email' => 'admin@example.test'
        ]);

        $users [] =  $test_admin->makeVisible(['password'])
        ->makeHidden(['profile_photo_url'])
        ->toArray();
        
        User::upsert(
            $data = $users,
            uniqueBy: ['phone_number', 'email'],
            update: []
        );

        /**
         * Create the student account
         */
        $user_stdn = User::where('email','student@example.test')
        ->first();

        if(!is_null($user_stdn) &&  is_null($user_stdn->student))
        {
           $student =  Student::factory()->create([
                'user_id' => $user_stdn->id,
            ]);
        }

        /**
         * Create the teacher account
         */
        $user_teacher = User::where('email','teacher@example.test')
        ->first();

        if(!is_null($user_teacher) &&  is_null($user_teacher->teacher))
        {
           $teacher =  Teacher::factory()->create([
                'user_id' => $user_teacher->id,
            ]);
        }

         /**
         * Assigned admin role to the admin user
         */

        $admin = 'admin';
    
        Role::upsert(
            $roles=[['name'=>$admin,'guard_name' => config('auth.defaults.guard')]],
            uniqueBy:['name', 'guard_name'],
            update: (new Role)->getFillable()
        );

        $test_admin = User::where('email', 'admin@example.test')->first();
        $test_admin->assignRole($admin);

    }
}
