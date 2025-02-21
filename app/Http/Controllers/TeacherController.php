<?php

namespace App\Http\Controllers;

use App\Http\Resources\TeacherResource;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Teacher;
use App\Models\User;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Teacher::class, 'teacher');
    }

    /**
     * Display a listing of the resource.
     * 
     * @method GET|HEAD /api/teachers
     */
    public function index()
    {
        $paginate = request()->has('paginate') ? request()->paginate : true; 
        $per_page = request()->has('per_page') ? request()->per_page : 15; 

        $teachers = QueryBuilder::for(Teacher::class)
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
            $teachers->where(function($query){
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

            $teachers = $teachers->paginate($per_page)
            ->appends(request()->query());

        }
        else { $teachers = $teachers->get(); }
        
        $teachers_collection = TeacherResource::collection($teachers)->additional([
            'status' => 'success',
            'message' => 'Teachers retrieved successfully'
        ]);

        return $teachers_collection;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeacherRequest $request)
    {
        $validated = $request->validated();

        try {

            DB::beginTransaction();

            $password = 'password';
            $validated['password'] = Hash::make($password);

            $user = User::create($validated);
            $teacher = $user->teacher()->create($validated);  
            $teacher->load(['user']);

            $teacher_resource = (new TeacherResource($teacher))->additional([
                'message' => 'Teacher created successfully'
            ]);

            $user->notify(new \App\Notifications\Auth\PasswordCreated($user,$password));

            DB::commit();

            return $teacher_resource;

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
    public function show(Teacher $teacher)
    {
        $teacher->applyRequestIncludesAndAppends();

        $teacher_resource = (new TeacherResource($teacher))->additional([
            'message' => 'Teacher retrieved successfully'
        ]);

        return $teacher_resource;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        $validated = $request->validated();

        try {

            DB::beginTransaction();

            $teacher->user->update($validated);
            $teacher->update($validated);

            $teacher_resource = (new TeacherResource($teacher))->additional([
                'message' => 'Teacher updated successfully'
            ]);
    
            DB::commit();

            return $teacher_resource;

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
    public function destroy(Teacher $teacher)
    {
        $teacher->delete();

        $teacher_resource = (new TeacherResource(null))->additional([
            'message' => 'Teacher deleted successfully'
        ]);

        return $teacher_resource;
    }
}
