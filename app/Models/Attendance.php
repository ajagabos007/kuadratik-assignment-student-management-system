<?php

namespace App\Models;

use App\Observers\AttendanceObserver;
use App\Traits\ModelRequestLoader;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy([AttendanceObserver::class])]
class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\teacherFactory> */
    use HasFactory;
    use HasUuids;
    use ModelRequestLoader;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'time_in',
        'time_out',
    ];


    /**
     * Get the user account of the teacher
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeStudent(Builder $query): Builder
    {
        return $query->whereHas('user', function($query){
            $query->whereHas('student');
        });
    }

}
