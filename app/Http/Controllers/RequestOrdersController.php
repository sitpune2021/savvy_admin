<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contracts;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;

class RequestOrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
        $query = Contracts::with('customer', 'product', 'sender')->where('type', 'additional')->orderBy('created_at', 'desc'); // or 'id', depending on your use case
        // ->get();

        return DataTables::of($query)
        ->addIndexColumn()
        ->addColumn('customer_name', fn($contract) => $contract->customer?->name)
        ->addColumn('shipping_address', fn($contract) => $contract->shippingAddress?->shipping_address)
        ->addColumn('sender_name', fn($contract) => $contract->sender?->name)
        ->addColumn('product_name', fn($contract) => $contract->product?->name)
        ->addColumn('quantity', fn($contract) => $contract->quantity)
        ->addColumn('accepted_status', function ($contract) {
            $status = $contract->accepted_status;
            $statusClasses = [
                'expired' => 'bg-danger-subtle text-danger',
                'rejected' => 'bg-danger-subtle text-danger',
                'pending' => 'bg-warning-subtle text-warning',
                'accepted' => 'bg-success-subtle text-success',
                'active' => 'bg-success-subtle text-success',
                'in-progress' => 'bg-info-subtle text-info',
            ]; 
            $form = '';
            if ($contract->accepted_status === 'active') {
                $form = '
                    <form action="' . route('requestOrder.update.status', $contract->id) . '" method="POST" class="status-form d-none">
                        ' . csrf_field() . method_field('PUT') . '
                        <select name="accepted_status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="pending" ' . ($status == 'pending' ? 'selected' : '') . '>Pending</option>
                            <option value="accepted" ' . ($status == 'accepted' ? 'selected' : '') . '>Accepted</option>
                            <option value="rejected" ' . ($status == 'rejected' ? 'selected' : '') . '>Rejected</option>
                        </select>
                    </form>';
            }

            return '
                <div class="status-wrapper" ' . ($contract->accepted_status === 'active' ? 'ondblclick="toggleEdit(this)"' : '') . '>
                    <span class="badge ' .  ($statusClasses[$contract->accepted_status] ?? 'bg-secondary-subtle text-secondary') . ' p-2 status-badge">' . ucfirst(str_replace('_', ' ', $status)) . '</span>
                    ' . $form . '
                </div>';
        })
        ->addColumn('date', fn($contract) => $contract->date)
        ->addColumn('driver_name', fn($contract) => $contract->shippingAddress?->driver?->name)
        ->addColumn('status', function ($contract) {
            $statusClasses = [
                'expired' => 'bg-danger-subtle text-danger',
                'rejected' => 'bg-danger-subtle text-danger',
                'pending' => 'bg-warning-subtle text-warning',
                'accepted' => 'bg-success-subtle text-success',
                'active' => 'bg-success-subtle text-success',
                'in-progress' => 'bg-info-subtle text-info',
            ]; 
            return '<span class="badge ' . ($statusClasses[$contract->status] ?? 'bg-secondary-subtle text-secondary')  . ' p-2 status-badge">' . ucfirst(str_replace('_', ' ', $contract->status)) . '</span>';
        })
        ->filter(function ($query) use ($request) {
                if ($search = $request->get('search')['value']) {
                    $query->where(function ($q) use ($search) {
                        $q->whereHas('customer', fn($q) => $q->where('name', 'like', "%{$search}%"))
                          ->orWhereHas('product', fn($q) => $q->where('name', 'like', "%{$search}%"))
                          ->orWhereHas('sender', fn($q) => $q->where('name', 'like', "%{$search}%"))
                          ->orWhereHas('shippingAddress', fn($q) =>
                              $q->where('shipping_address', 'like', "%{$search}%")
                                ->orWhereHas('driver', fn($q) => $q->where('name', 'like', "%{$search}%"))
                          )
                          ->orWhere('accepted_status', 'like', "%{$search}%")
                          ->orWhere('status', 'like', "%{$search}%")
                          ->orWhere('quantity', 'like', "%{$search}%")
                          ->orWhere('date', 'like', "%{$search}%");
                    });
                }
            })
        ->rawColumns(['accepted_status', 'status'])
        ->make(true);
        
        }
        return  view('pages.requestOrders.index');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'accepted_status' => 'required|in:pending,accepted,rejected',
        ]);

        $maintenance = Contracts::findOrFail($id);
        $maintenance->accepted_status = $request->input('accepted_status');
        $maintenance->save();
        Http::get('https://savvywater.demosoftware.co.in/schedule');

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
