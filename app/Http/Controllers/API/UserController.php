<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

use App\User;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return User::all();
        if (\Gate::allows('isSadmin') || \Gate::allows('isAdmin')) 
        {
            return User::where('status', 1)->orderBy('id', 'desc')->paginate();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:8'
        ]);
        // return $request;
        return User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'bio' => $request['bio'],
            'photo' => $request['photo'],
            'type' => $request['type'],
            'password' => Hash::make($request['password'])
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->validate($request,[
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,'.$user->id,
            'password' => 'sometimes|min:6'
        ]);

        if(!empty($request->password)){
            $request->merge(['password' => Hash::make($request['password'])]);
        }
        
        $user->update($request->all());
        return ['message' => 'Updated the user info'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (\Gate::allows('isSadmin') || \Gate::allows('isAdmin')) 
        {
            $user = User::findOrFail($id);
            // Delete user
            $user->delete();

            return ['message' => 'User Deleted'];
        }
    }

    public function profile()
    {
        return auth('api')->user();
    }

    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();

        $this->validate($request,[
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,'.$user->id,
            'password' => 'sometimes|required|min:6'
        ]);

        $currentPhoto = $user->photo;

        if($request->photo != $currentPhoto) 
        {
            $ext = explode('/', explode(':', substr($request->photo, 0, strpos($request->photo, ';')))[1])[1];

            $profilename = time() . ".{$ext}";
            // move new photo to new folder 
            \Image::make($request->photo)->save(public_path('img/profile/').$profilename);    
            $request->merge(['photo' => $profilename]);

            $userPhoto = public_path('img/profile/').$currentPhoto;
            // Deleter prev photo
            if(file_exists($userPhoto))
            {
                @unlink($userPhoto);
            }
            $currentPhoto = $profilename;
        }

        if(!empty($request->password))
        {
            $request->merge(['password' => Hash::make($request['password'])]);
        }
        
        $user->update($request->all());
        return ['message' => 'success', 'profile' => $currentPhoto];
    }

    public function companyList() 
    {
        if (\Gate::allows('isSadmin') || \Gate::allows('isAdmin')) 
        {
            return User::where('type', 'user')
                        ->where('status', 1)
                        ->orderBy('id', 'desc')
                        ->paginate();
        }
    }
}
