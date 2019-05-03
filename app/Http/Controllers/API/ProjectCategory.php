<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ProjectCategories;

class ProjectCategory extends Controller
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
        if (\Gate::allows('isSadmin') || \Gate::allows('isAdmin')) 
        {
            return ProjectCategories::where('status', 1)->orderBy('id', 'desc')->paginate();
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
            'category_name' => 'required|string|max:255',
        ]);
        // return $request;
        return ProjectCategories::create([
            'category_name' => $request['category_name'],
            'description' => $request['description'],
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
        $cat = ProjectCategories::findOrFail($id);
        $this->validate($request,[
            'category_name' => 'required|string|max:191'
        ]);
        
        $cat->update($request->all());
        return ['message' => 'Updated the category info'];
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
            $cat = ProjectCategories::findOrFail($id);
            // Delete user
            if($cat)
            {
                $cat->status = 0;
                $cat->save();
                return ['message' => 'Category Deleted'];
            }

            
        }
    }
}
