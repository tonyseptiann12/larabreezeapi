<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $users = User::all();
        $users = User::when($request->search, function($query, $search){
            $query->where('name', 'like', '%'.$search.'%')
                  ->orWhere('email', 'like', '%'.$search.'%');
        })->paginate(5);

        $data = UserResource::collection($users)->resource;

        return $this->sendResponse($data, 'Successfully', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $data = $request->validated();
        if($request->profile_photo_path != null){
            $fileName = time().'.'.$request->profile_photo_path->extension();

            $request->profile_photo_path->move(public_path('uploads'), $fileName);

            $data['profile_photo_path'] = $fileName;
        }

        $data['password'] = Hash::make('password');
        $user = User::create($data);

        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user = new UserResource($user);
        $roles = Role::all();
        $userRole = $user->roles->pluck('name', 'name')->all(); 

        $data['user'] = $user;
        $data['roles'] = $roles;
        $data['userRole'] = $userRole;

        return $this->sendResponse($data, 'Successfully', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();
        if($request->profile_photo_path != null){
            $fileName = time().'.'.$request->profile_photo_path->extension();

            $request->profile_photo_path->move(public_path('uploads'), $fileName);

            $data['profile_photo_path'] = $fileName;
        }

        if($request->password != null){
            $data['password'] = Hash::make($request->password);
        }

        $data['level'] = $request->level;

        $user->update($data);

        DB::table('model_has_roles')->where('model_id', $user->id)->delete();

        $user->assignRole($request->roles);

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $userAuth = User::find(Auth::user()->id);
        if($userAuth->can('user-delete')){
            $user->delete();

            return $this->sendResponse('', 'Berhasil dihapus',200);
        } else {
            return $this->sendError('Have no access to delete', null, 403);
        }
    }
}