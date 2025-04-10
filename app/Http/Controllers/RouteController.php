<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Routes;
use Illuminate\Support\Facades\Storage;
use Exception;

class RouteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $routes = Routes::all();
        return view('pages.route.index', compact('routes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $show = false;        
        return view('pages.route.add-edit',compact('show'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'path' => 'required|string|max:255',
        ]);

    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->all();
            Routes::create($data);
            return redirect()->route('route.index')->with('success', 'Route added successfully.');
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
        return view('pages.route.add-edit',compact('show', 'Route'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $show = false;
            $Route = Routes::findOrFail($id);
            return view('pages.route.add-edit',compact('show', 'Route'));
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
            'name' => 'required|string|max:255',
            'path' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $Route = Routes::findOrFail($id);
            $data = $request->all();
            $Route->update($data);
            return redirect()->route('route.index')->with('success', 'Route updated successfully.');
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
