<?php

namespace App\Observers;

use App\Models\Student;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class StudentObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the student "created" event.
     */
    public function created(Student $student): void
    {

    }

    /**
     * Handle the student "updated" event.
     */
    public function updated(Student $student): void
    {

    }

    /**
     * Handle the student "deleted" event.
     */
    public function deleted(Student $student): void
    {
        $student->user->delete();
    }

    /**
     * Handle the student "restored" event.
     */
    public function restored(Student $student): void
    {
        //
    }

    /**
     * Handle the student "force deleted" event.
     */
    public function forceDeleted(Student $student): void
    {
        //
    }
}
