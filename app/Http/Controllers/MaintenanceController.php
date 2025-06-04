<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Maintenance;

class MaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $maintenances = Maintenance::with('driver')->orderBy('created_at', 'desc')->get();
        return view('pages.Maintenance.list', compact('maintenances'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $maintenance = Maintenance::findOrFail($id);
        $maintenance->status = $request->input('status');
        $maintenance->save();

        return redirect()->back()->with('success', 'Status updated.');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function check(){
        return view('image');

    }
    public function upload(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle the uploaded image
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            // Generate a unique file name
            $filename = time() . '.' . $image->getClientOriginalExtension();

            // Save the image in the 'public/images' directory
            $path = $image->storeAs('images', $filename, 'public');

            // You can save $path to your database here if needed

            return back()->with('success', 'Image uploaded successfully')->with('path', $path);
        }

        return back()->withErrors('No image selected');
    }
}

