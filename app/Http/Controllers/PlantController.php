<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plant;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PlantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Plants = Plant::all();
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
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'manager' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'pincode' => 'required|string|min:6|max:20',
            'details' => 'nullable|string|max:255',
        ]);

    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->all();
            Plant::create($data);
            
            return redirect()->route('plant.index')->with('success', 'Plant added successfully.');
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
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'manager' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'pincode' => 'required|string|min:6|max:20',
            'details' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {

            $Plant = Plant::findOrFail($id);
            $Plant->update($request->all());
            
            return redirect()->route('plant.index')->with('success', 'Plant updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to update plant: '.$e->getMessage());
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
