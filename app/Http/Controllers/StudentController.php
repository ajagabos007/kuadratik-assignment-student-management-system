<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentResource;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Student;
use App\Models\User;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Student::class, 'student');
    }

    /**
     * Display a listing of the resource.
     * 
     * @method GET|HEAD /api/students
     */
    public function index()
    {
        $paginate = request()->has('paginate') ? request()->paginate : true; 
        $per_page = request()->has('per_page') ? request()->per_page : 15; 

        $students = QueryBuilder::for(Student::class)
        ->defaultSort('-created_at')
        ->allowedSorts(
            'registration_no',         
            'created_at',
            'updated_at',
        )
        ->allowedFilters([
            'user.id',     
        ])
        ->allowedIncludes([
            'user',  
        ]);

        if(request()->has('q'))
        {
            $students->where(function($query){
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

            $students = $students->paginate($per_page)
            ->appends(request()->query());

        }
        else { $students = $students->get(); }
        
        $students_collection = StudentResource::collection($students)->additional([
            'status' => 'success',
            'message' => 'Students retrieved successfully'
        ]);

        return $students_collection;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request)
    {
        $validated = $request->validated();

        try {

            DB::beginTransaction();

            $password = 'password';
            $validated['password'] = Hash::make($password);

            $user = User::create($validated);
            $student = $user->student()->create($validated);  
            $student->load(['user']);

            $student_resource = (new StudentResource($student))->additional([
                'message' => 'Student created successfully'
            ]);

            $user->notify(new \App\Notifications\Auth\PasswordCreated($user,$password));

            DB::commit();

            return $student_resource;

        } catch (\Throwable $th) {
            
            DB::rollBack();
            
            \Log::error($th);
            
            return response()->json([
                'error' => $th->getMessage(),
                'message' => $th->getMessage()
            ], 500);
        }        
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        $student->applyRequestIncludesAndAppends();

        $student_resource = (new StudentResource($student))->additional([
            'message' => 'Student retrieved successfully'
        ]);

        return $student_resource;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student)
    {
        $validated = $request->validated();

        try {

            DB::beginTransaction();

            $student->user->update($validated);
            $student->update($validated);

            $student_resource = (new StudentResource($student))->additional([
                'message' => 'Student updated successfully'
            ]);
    
            DB::commit();

            return $student_resource;

        } catch (\Throwable $th) {
            
            DB::rollBack();
            
            \Log::error($th);
            
            return response()->json([
                'error' => $th->getMessage(),
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        $student->delete();

        $student_resource = (new StudentResource(null))->additional([
            'message' => 'Student deleted successfully'
        ]);

        return $student_resource;
    }
}
