<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contracts;

class RequestOrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contracts = Contracts::with('customer', 'product', 'sender')->where('type', 'additional')->orderBy('created_at', 'desc') // or 'id', depending on your use case
        ->get();
        return  view('pages.requestOrders.index', compact('contracts'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'accepted_status' => 'required|in:pending,accepted,rejected',
        ]);

        $maintenance = Contracts::findOrFail($id);
        $maintenance->accepted_status = $request->input('accepted_status');
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
}
