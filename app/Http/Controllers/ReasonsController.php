<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Reasons;
use Exception;

class ReasonsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Reasons::orderBy('created_at', 'desc');
        $Reasons = $query->get();
        return view('pages.Settings.Reasons.index', compact('Reasons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $show = false; 
        return view('pages.Settings.Reasons.add-edit',compact('show'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'for' => 'required|string|max:255',
            'reasons' => 'required|string|max:255',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->all();
            Reasons::create($data);
            return response()->json(['message' => 'Reason added successfully'], 200);
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to add Reason: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
         $show = true;
        $Reason = Reasons::findOrFail($id);
        return view('pages.Settings.Reasons.add-edit',compact('show', 'Reason'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $show = false;
            $Reason = Reasons::findOrFail($id);
        return view('pages.Settings.Reasons.add-edit',compact('show', 'Reason'));
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Reason not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while fetching the Reason for editing: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $validator = Validator::make($request->all(), [
            'for' => 'required|string|max:255',
            'reasons' => 'required|string|max:255',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $Reason = Reasons::findOrFail($id);
            $data = $request->all(); 
            $Reason->update($data);
            return response()->json(['message' => 'Reason updated successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['errors' => 'Failed to update Reason: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $Reason = Reasons::findOrFail($id);
            $Reason->delete();
            return redirect()->route('reasons.index')->with('success', 'Reason deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Reason not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while deleting the Reason: ' . $e->getMessage()]);
        }
    }
}
