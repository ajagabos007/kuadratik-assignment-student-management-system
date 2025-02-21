<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use function Illuminate\Support\defer;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Builder|Relation|string $subject=null)
    {
        $paginate = request()->has('paginate') ? request()->paginate : true; 
        $per_page = request()->has('per_page') ? request()->per_page : 15; 

        $users = QueryBuilder::for($subject?? User::class)
        ->defaultSort('-created_at')
        ->allowedSorts(
            'first_name',
            'last_name',
            'middle_name',
            'phone_number',
            'email',
            'created_at',
            'updated_at',
        )
        ->allowedIncludes([
           'student',
           'teacher'
        ])
        ->allowedFilters([
            'first_name',
            'last_name',
            'middle_name',
            'phone_number',
            'email',
        ]);

        if(request()->has('q'))
        {
            $users->where(function($query){
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

            $users = $users->paginate($per_page)
            ->appends(request()->query());

        }
        else { $users = $users->get(); }
        
        $users_collection = UserResource::collection($users)->additional([
            'message' => 'User retrieved successfully'
        ]);

        return $users_collection;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->applyRequestIncludesAndAppends();

        $user_resource = (new UserResource($user))->additional([
            'message' => 'User retreived successfully'
        ]);

        return $user_resource;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    /**
     * Get logged in user profile
     * 
     * @method GET /api/user/profile
     * 
     * @return \App\Http\Resources\UserResource
     */
    public function profile(Request $request)
    {

        $user = auth()->user();

        if(!is_null($user))
        {
            $user->applyRequestIncludesAndAppends();
        }
        
        $user_resource = (new UserResource($user))->additional([
            'message' => 'Profile retreived successfully'
        ]);

        return $user_resource;
    }

    /**
     * Update auth user profile
     * @method POST api/user/profile
     * 
     * @return \App\Http\Resources\UserResource
     */
    public function updateProfile(UpdateUserRequest $request)
    {
        $validated = $request->validated();
        
        if(!is_null($user = auth()->user()))
        {
            $user->update($validated);

            if(array_key_exists('profile_photo', $validated))
            {
               $user->updateProfilePhoto($validated['profile_photo']);
            }

            $user->append('profile_photo_url');
        }
        
        $user_resource = (new UserResource($user))->additional([
            'message' => 'Profile updated successfully'
        ]);

        return $user_resource;
    }
}
