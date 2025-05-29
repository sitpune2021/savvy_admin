<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Routes;
use App\Models\Plant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Exception;

class RouteController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Routes::orderBy('created_at', 'desc');
        if($this->plantManagerId){
            $query->where('plant_id', $this->plantManagerId);
        }else{
            if ($this->vendorId !== null) {
                $query->where('vendor_id', $this->vendorId);
            }
        }
        
        $routes = $query->get();
        return view('pages.route.index', compact('routes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $show = false;   
        $query = Plant::orderBy('created_at', 'desc');
        if($this->plantManagerId){
            $query->where('id', $this->plantManagerId);
        }else{
            if ($this->vendorId !== null) {
                $query->where('vendor_id', $this->vendorId);
            }
        }
        
        $plants = $query->get();    
        return view('pages.route.add-edit',compact('show' , 'plants'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('routes')->whereNull('deleted_at'), // Excludes soft-deleted records
            ],
            'path' => 'required|string|max:255',
            'plant_id' => 'required|exists:plants,id',

        ]);

    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->all();
            if ($this->vendorId !== null) {
                $data['vendor_id'] = $this->vendorId;
            }
            $Plant = Plant::findOrFail($data['plant_id']);
            if($plants->vendor_id){
                $data['vendor_id'] = $plants->vendor_id;
            }
            Routes::create($data);
            return response()->json(['message'=>'Route added successfully.']);
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to add route: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $show = true;
        $Route = Routes::findOrFail($id);
        $query = Plant::orderBy('created_at', 'desc');
        if($this->plantManagerId){
            $query->where('id', $this->plantManagerId);
        }else{
            if ($this->vendorId !== null) {
                $query->where('vendor_id', $this->vendorId);
            }
        }
        $plants = $query->get();    
        return view('pages.route.add-edit',compact('show', 'Route', 'plants'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $show = false;
            $Route = Routes::findOrFail($id);
            $query = Plant::orderBy('created_at', 'desc');
            if($this->plantManagerId){
                $query->where('id', $this->plantManagerId);
            }else{
                if ($this->vendorId !== null) {
                    $query->where('vendor_id', $this->vendorId);
                }
            }
            $plants = $query->get();    
            return view('pages.route.add-edit',compact('show', 'Route', 'plants'));
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Route not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while fetching the Route for editing: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('routes')->ignore($id)->whereNull('deleted_at'),
            ],
            'path' => 'required|string|max:255',
            'plant_id' => 'required|exists:plants,id',

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $Route = Routes::findOrFail($id);
            $data = $request->all();
            $Plant = Plant::findOrFail($data['plant_id']);
            if($plants->vendor_id){
                $data['vendor_id'] = $plants->vendor_id;
            }else{
                $data['vendor_id'] = null;
            }
            $Route->update($data);
            return response()->json(['message'=>'Route updated successfully.']);

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to update route: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $Route = Routes::findOrFail($id);
            $Route->delete();
            return redirect()->route('route.index')->with('success', 'Route deleted successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete route: '.$e->getMessage());
        }
    }
}
