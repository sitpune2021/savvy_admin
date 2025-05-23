<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\Customers;
use App\Models\Drivers;
use App\Models\Plant;
use Carbon\Carbon;

class HomeController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Dates for monthly comparisons
        $startOfThisMonth = Carbon::now()->startOfMonth();
        $endOfThisMonth = Carbon::now()->endOfMonth();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();
        $today = Carbon::today();
    
        // Monthly orders
        $thisMonthOrders = Orders::forVendor($this->vendorId)->whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth])->count();
        $lastMonthOrders = Orders::forVendor($this->vendorId)->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $todayOrders = Orders::forVendor($this->vendorId)->whereDate('created_at', $today)->count();
        $yesterdayPendingOrders = Orders::forVendor($this->vendorId)->whereDate('created_at', Carbon::yesterday())->where('status', 'pending')->count();
        $allPendingOrders = Orders::forVendor($this->vendorId)->whereDate('created_at','!=', $today)->where('status', 'pending')->orderBy('created_at', 'desc')->get();
        $todayPendingOrders = Orders::forVendor($this->vendorId)->whereDate('created_at', $today)->where('status', 'pending')->count();
        $todayCompletedOrders = Orders::forVendor($this->vendorId)->whereDate('created_at', $today)->where('status', 'completed')->count();
        $todayInProgressOrders = Orders::forVendor($this->vendorId)->whereDate('created_at', $today)->where('status', 'in-progress')->count();

        // Monthly customers
        $thisMonthCustomersQuery = Customers::whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth]);
        if ($this->vendorId !== null) {
            $thisMonthCustomersQuery->whereHas('shippingAddresses', function($query) {
                $query->where('type', 'pan_india')
                    ->where('vendor_id', $this->vendorId);
            });
        }
        $thisMonthCustomers = $thisMonthCustomersQuery->count();
        $lastMonthCustomersQuery = Customers::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth]);
         if ($this->vendorId !== null) {
            $lastMonthCustomersQuery->whereHas('shippingAddresses', function($query) {
                $query->where('type', 'pan_india')
                    ->where('vendor_id', $this->vendorId);
            });
        }
        $lastMonthCustomers = $lastMonthCustomersQuery->count();
    
        // Percent change helper
        function percentChange($current, $previous) {
            if ($previous == 0) return $current > 0 ? 100 : 0;
            return round((($current - $previous) / $previous) * 100, 2);
        }
    
        // Calculate changes
        $orderChange = percentChange($thisMonthOrders, $lastMonthOrders);
        $customerChange = percentChange($thisMonthCustomers, $lastMonthCustomers);

        // Get orders with routes, filtered by vendor_id (either NULL or matching ID)
        $orders = Orders::whereHas('route', function ($query) {
                $query->when($this->vendorId !== null, function ($q) {
                    $q->where('vendor_id', $this->vendorId);
                }, function ($q) {
                    $q->whereNull('vendor_id');
                });
            })
            ->with('route')
            ->get();

        // Group and count orders by plant_id (safely handling null routes)
        $ordersCountByPlant = $orders->groupBy(function($order) {
                return optional($order->route)->plant_id;
            })
            ->map(function($group) {
                return $group->count();
            });

        // Get plants filtered by vendor_id (either NULL or matching)
        $plants = Plant::when($this->vendorId !== null, function ($query) {
                $query->where('vendor_id', $this->vendorId);
            }, function ($query) {
                $query->whereNull('vendor_id');
            })->pluck('name', 'id');

        // Make sure all plants are represented in the count, even if zero
        foreach ($plants as $plantId => $plantName) {
            if (!isset($ordersCountByPlant[$plantId])) {
                $ordersCountByPlant[$plantId] = 0;
            }
        }

        return view('home', compact('thisMonthOrders', 'todayOrders', 'todayPendingOrders','allPendingOrders', 'yesterdayPendingOrders',
        'todayCompletedOrders', 'todayInProgressOrders', 'orderChange',  'customerChange', 'thisMonthCustomers', 'ordersCountByPlant', 'plants'));
    }
}
