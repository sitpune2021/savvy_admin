<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plant;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;

class PlantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Plants = Plant::orderBy('created_at', 'desc')->get();
        return view('pages.plant.index', compact('Plants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $show = false;        
        return view('pages.plant.add-edit',compact('show'));
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
                'regex:/^[a-zA-Z\s\-]+$/',
                Rule::unique('plants')->whereNull('deleted_at'),
            ],
            'address' => 'required|string|max:255',
            'manager' => 'required|string|max:255|regex:/^[a-zA-Z\s\-]+$/',
            'location' => 'required|string|max:255',
            'pincode' => 'required|string|digits:6',
            'details' => 'nullable|string|max:255',
        ]);

    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->all();
            Plant::create($data);
            return response()->json(['message' => 'Plant added successfully'], 200);
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to add plant: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $show = true;
        $Plant = Plant::findOrFail($id);
        return view('pages.plant.add-edit',compact('show', 'Plant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $show = false;
            $Plant = Plant::findOrFail($id);
            return view('pages.plant.add-edit',compact('show', 'Plant'));
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Plant not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while fetching the Plant for editing: ' . $e->getMessage()]);
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
                'regex:/^[a-zA-Z\s\-]+$/',
                Rule::unique('plants', 'name')->ignore($id)->whereNull('deleted_at'),
            ],
            'address' => 'required|string|max:255',
            'manager' => 'required|string|max:255|regex:/^[a-zA-Z\s\-]+$/',
            'location' => 'required|string|max:255',
            'pincode' => 'required|string|digits:6',
            'details' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {

            $Plant = Plant::findOrFail($id);
            $Plant->update($request->all());
            return response()->json(['message' => 'Plant updated successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['errors' => 'Failed to update plant: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $Plant = Plant::findOrFail($id);
            $Plant->delete();
            return redirect()->route('plant.index')->with('success', 'Plant deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Plant not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while deleting the Plant: ' . $e->getMessage()]);
        }
    }
}
