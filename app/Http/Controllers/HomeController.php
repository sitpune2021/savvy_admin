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
    public function index(Request $request)
    {
        $startOfThisMonth = Carbon::now()->startOfMonth();
        $endOfThisMonth = Carbon::now()->endOfMonth();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();
        $today = Carbon::today();

        $userRole = auth()->user()->role;
        $isAdmin = ($userRole === 'admin');

        $type = $request->query() ? $request->query('value') : 'all';
        if($this->plantManagerId)
        {
            $baseQuery = Orders::forPlantManager($this->plantManagerId);
        } else {
            $baseQuery = Orders::forVendor($this->vendorId, $isAdmin, $type);
        } 

        $thisMonthOrders = (clone $baseQuery)->whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth])->count();
        $lastMonthOrders = (clone $baseQuery)->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $todayOrders = (clone $baseQuery)->whereDate('created_at', $today)->count();

        $yesterdayPendingOrders = (clone $baseQuery)->whereDate('created_at', Carbon::yesterday())
            ->where('status', 'pending')->count();

        $allPendingOrders = (clone $baseQuery)->whereDate('created_at', '!=', $today)
            ->where('status', 'pending')->orderBy('created_at', 'desc')->get();

        $todayPendingOrders = (clone $baseQuery)->whereDate('created_at', $today)
            ->where('status', 'pending')->count();

        $todayCompletedOrders = (clone $baseQuery)->whereDate('created_at', $today)
            ->where('status', 'completed')->count();

        $todayInProgressOrders = (clone $baseQuery)->whereDate('created_at', $today)
            ->where('status', 'in-progress')->count();

        $thisMonthCustomersQuery = Customers::whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth]);
        if ($this->plantManagerId) {
            $thisMonthCustomersQuery->whereHas('shippingAddresses', function ($query) {
                        $query->where('plant_id', $this->plantManagerId);
                    });
         }else{
            if ($isAdmin) {
                $thisMonthCustomersQuery->whereHas('shippingAddresses', function ($query) use ($type) {
                    if ($type === 'pan_india') {
                        $query->whereNotNull('vendor_id');
                    } elseif($type === 'local') {
                        $query->whereNull('vendor_id');
                    }
                    else{
                        $query;
                    }

                });
            } else {
                if ($this->vendorId !== null) {
                    $thisMonthCustomersQuery->whereHas('shippingAddresses', function ($query) {
                        $query->where('vendor_id', $this->vendorId);
                    });
                }
            }
        }

        $thisMonthCustomers = $thisMonthCustomersQuery->count();

        $lastMonthCustomersQuery = Customers::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth]);
         if ($this->plantManagerId) {
            $lastMonthCustomersQuery->whereHas('shippingAddresses', function ($query) {
                        $query->where('plant_id', $this->plantManagerId);
                    });
         }else{
          if ($isAdmin) {
                $lastMonthCustomersQuery->whereHas('shippingAddresses', function ($query) use ($type) {
                    if ($type === 'pan_india') {
                        $query->whereNotNull('vendor_id');
                    } elseif($type === 'local') {
                        $query->whereNull('vendor_id');
                    } else {
                        $query;
                    }
                });
            } else {
                if ($this->vendorId !== null) {
                    $lastMonthCustomersQuery->whereHas('shippingAddresses', function ($query) {
                        $query->where('vendor_id', $this->vendorId);
                    });
                }
            }
        }
        $lastMonthCustomers = $lastMonthCustomersQuery->count();

        $percentChange = function ($current, $previous) {
            if ($previous == 0) return $current > 0 ? 100 : 0;
            return round((($current - $previous) / $previous) * 100, 2);
        };

        $orderChange = $percentChange($thisMonthOrders, $lastMonthOrders);
        $customerChange = $percentChange($thisMonthCustomers, $lastMonthCustomers);

        if ($this->plantManagerId) {
            $orders = Orders::whereHas('route', function ($query) {
                $query->where('plant_id', $this->plantManagerId);
            })->with('route')->get();

            $ordersCountByPlant = $orders->groupBy(function ($order) {
                return optional($order->route)->plant_id;
            })->map->count();

            $plants = Plant::where('id', $this->plantManagerId)->pluck('name', 'id');

        } else {
            $orders = Orders::whereHas('route', function ($query) use ($isAdmin, $type) {
                if ($isAdmin) {
                    if ($type === 'pan_india') {
                        $query->whereNotNull('vendor_id');
                    } elseif ($type === 'local') {
                        $query->whereNull('vendor_id');
                    }
                } else {
                    $query->when($this->vendorId !== null, function ($q) {
                        $q->where('vendor_id', $this->vendorId);
                    }, function ($q) {
                        $q->whereNull('vendor_id');
                    });
                }
            })->with('route')->get();

            $ordersCountByPlant = $orders->groupBy(function ($order) {
                return optional($order->route)->plant_id;
            })->map->count();

            $plants = Plant::when($isAdmin, function ($query) use ($type) {
                if ($type === 'pan_india') {
                    $query->whereNotNull('vendor_id');
                } elseif ($type === 'local') {
                    $query->whereNull('vendor_id');
                }
            }, function ($query) {
                $query->when($this->vendorId !== null, function ($q) {
                    $q->where('vendor_id', $this->vendorId);
                }, function ($q) {
                    $q->whereNull('vendor_id');
                });
            })->pluck('name', 'id');
        }

        foreach ($plants as $plantId => $plantName) {
            if (!isset($ordersCountByPlant[$plantId])) {
                $ordersCountByPlant[$plantId] = 0;
            }
        }

        $data = compact(
            'thisMonthOrders',
            'todayOrders',
            'todayPendingOrders',
            'allPendingOrders',
            'yesterdayPendingOrders',
            'todayCompletedOrders',
            'todayInProgressOrders',
            'orderChange',
            'customerChange',
            'thisMonthCustomers',
            'ordersCountByPlant',
            'plants'
        );

        if ($request->ajax()) {
            return response()->json($data);
        }

        return view('home', $data);
    }

}
