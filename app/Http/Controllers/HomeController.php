<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\Customers;
use App\Models\Drivers;
use App\Models\Plant;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

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
        $thisMonthOrders = Orders::whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth])->count();
        $lastMonthOrders = Orders::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $todayOrders = Orders::whereDate('created_at', $today)->count();
        $yesterdayPendingOrders = Orders::whereDate('created_at', Carbon::yesterday())->where('status', 'pending')->get();
        $todayPendingOrders = Orders::whereDate('created_at', $today)->where('status', 'pending')->count();
        $todayCompletedOrders = Orders::whereDate('created_at', $today)->where('status', 'completed')->count();
    
        // Monthly customers
        $thisMonthCustomers = Customers::whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth])->count();
        $lastMonthCustomers = Customers::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
    
        // Percent change helper
        function percentChange($current, $previous) {
            if ($previous == 0) return $current > 0 ? 100 : 0;
            return round((($current - $previous) / $previous) * 100, 2);
        }
    
        // Calculate changes
        $orderChange = percentChange($thisMonthOrders, $lastMonthOrders);
        $customerChange = percentChange($thisMonthCustomers, $lastMonthCustomers);

        $ordersCountByPlant = Orders::with('route')
        ->get()
        ->groupBy(function($order) {
            return $order->route->plant_id; 
        })
        ->map(function($group) {
            return $group->count(); 
        });

    $plants = Plant::pluck('name', 'id'); 

    foreach ($plants as $plantId => $plantName) {
        if (!isset($ordersCountByPlant[$plantId])) {
            $ordersCountByPlant[$plantId] = 0;
        }
    }


    return view('home', compact('thisMonthOrders', 'todayOrders', 'todayPendingOrders', 'yesterdayPendingOrders',
    'todayCompletedOrders', 'orderChange',  'customerChange', 'thisMonthCustomers', 'ordersCountByPlant', 'plants'));
    }
}
