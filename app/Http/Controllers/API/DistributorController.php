<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\Distributor;

class DistributorController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 25);
        $page = $request->query('page', 1);
        $query = Distributor::orderBy('created_at', 'desc');

        if($this->plantManagerId){
            $query->whereHas('orders', function($q){
                $q->where('plant_id', $this->plantManagerId);
            });
        }
        $distributors = $this->plantManagerId ? $query->paginate($perPage, ['*'], 'page', $page) : $query->get();
        if ($distributors->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No distributors found'
            ], 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'Distributors retrieved successfully',
            'data' => $distributors
        ], 200);
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
