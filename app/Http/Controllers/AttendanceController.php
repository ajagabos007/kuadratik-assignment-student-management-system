<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttendanceResource;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\User;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class AttendanceController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Attendance::class, 'attendance');
    }

    /**
     * Display a listing of the resource.
     * 
     * @method GET|HEAD /api/attendances
     */
    public function index()
    {
        $paginate = request()->has('paginate') ? request()->paginate : true; 
        $per_page = request()->has('per_page') ? request()->per_page : 15; 

        $attendances = QueryBuilder::for(Attendance::class)
        ->defaultSort('-created_at')
        ->allowedSorts(
            'time_in',         
            'time_out',         
            'created_at',
            'updated_at',
        )
        ->allowedFilters([
            'user_id',   
            AllowedFilter::scope('student'),
        ])
        ->allowedIncludes([
            'user',
            'user.student'  
        ]);

        if(request()->has('q'))
        {
            $attendances->where(function($query){
                $table_cols_key = $query->getModel()->getTable()."_column_listing";

                if(Cache::has($table_cols_key)) { $cols = Cache::get($table_cols_key); }
                else 
                {
                    $cols = Schema::getColumnListing($query->getModel()->getTable());
                    Cache::put($table_cols_key, $cols);
                }

                $counter = 0;
                foreach($cols as $col){

                    if($counter == 0)
                        $query->where($col, 'LIKE', "%".request()->q."%");
                     else 
                        $query->orWhere($col, 'LIKE', "%".request()->q."%");
                    $counter ++;
                }
            })
            ->orWhereHas('user', function($query){
                $table_cols_key = $query->getModel()->getTable()."_column_listing";

                if(Cache::has($table_cols_key)) { $cols = Cache::get($table_cols_key); }
                else 
                {
                    $cols = Schema::getColumnListing($query->getModel()->getTable());
                    Cache::put($table_cols_key, $cols);
                }

                $counter = 0;
                foreach($cols as $col){

                    if($counter == 0)
                        $query->where($col, 'LIKE', "%".request()->q."%");
                        else 
                        $query->orWhere($col, 'LIKE', "%".request()->q."%");
                    $counter ++;
                }
            });
        }

        /**
         * Check if pagination is not disabled 
         */
        if(!in_array($paginate, [false, 'false', 0, '0'], true))
        {
            /** 
             * Ensure per_page is integer and >= 1 
             */
            if(!is_numeric($per_page)) $per_page = 15;
            else {
                $per_page = intval($per_page);
                $per_page = $per_page >=1? $per_page : 15 ;
            } 

            $attendances = $attendances->paginate($per_page)
            ->appends(request()->query());

        }
        else { $attendances = $attendances->get(); }
        
        $attendances_collection = AttendanceResource::collection($attendances)->additional([
            'status' => 'success',
            'message' => 'Attendances retrieved successfully'
        ]);

        return $attendances_collection;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttendanceRequest $request)
    {
        $validated = $request->validated();

        $attendance = null;

        if(array_key_exists('time_in', $validated))
        {
            
            $attendance = Attendance::where('user_id', $validated['user_id'])
            ->whereDate('time_in', $validated['time_in'])
            ->first();

        }
        else {
            $attendance = Attendance::where('user_id', $validated['user_id'])
            ->whereDate('time_in', now())
            ->first();
        }

        if(is_null($attendance))
        {
            $attendance = Attendance::create($validated);  
        }
        else {
            $attendance->save();
        }

        $attendance_resource = (new AttendanceResource($attendance))->additional([
            'message' => 'Attendance created successfully'
        ]);

        return $attendance_resource;
    }

    

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        $attendance->applyRequestIncludesAndAppends();

        $attendance_resource = (new AttendanceResource($attendance))->additional([
            'message' => 'Attendance retrieved successfully'
        ]);

        return $attendance_resource;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        $validated = $request->validated();

        $attendance->update($validated);
        
        $attendance_resource = (new AttendanceResource($attendance))->additional([
            'message' => 'Attendance updated successfully'
        ]);

        return $attendance_resource;
    }

    /**
     * Update the specified resource in storage.
     */
    public function signOut(Attendance $attendance)
    {

        if(is_null($attendance->time_out))
        {
            $attendance->time_out = now();
            $attendance->save();

        }
        
        $attendance_resource = (new AttendanceResource($attendance))->additional([
            'message' => 'Attendance signed out successfully'
        ]);

        return $attendance_resource;
    }

    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        $attendance_resource = (new AttendanceResource(null))->additional([
            'message' => 'Attendance deleted successfully'
        ]);

        return $attendance_resource;
    }
}
