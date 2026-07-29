<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\Customers;
use App\Models\Drivers;
use App\Models\Plant;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\DigitalCard;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use App\Models\RawStockForPlant;
use App\Models\JarMaintance;


class HomeController extends BaseController
{
    public function index(Request $request)
    {
        $dates = $this->getDateRanges();
        $userRole = auth()->user()->role;
        $isAdmin = ($userRole === 'admin');
        $type = $request->query('value', 'all');

        $dashboardPlants = Plant::when($this->plantManagerId, fn($query) =>
                $query->where('id', $this->plantManagerId)
            )
            ->orderBy('name')
            ->get(['id', 'name']);

        $defaultPlant = $dashboardPlants->first(fn($plant) =>
            strcasecmp(trim($plant->name), 'Warje') === 0
        ) ?? $dashboardPlants->first();

        $requestedPlantId = (int) $request->query('plant_id', $defaultPlant?->id);
        $selectedPlant = $dashboardPlants->firstWhere('id', $requestedPlantId) ?? $defaultPlant;
        $selectedPlantId = $selectedPlant?->id;

        $baseQuery = $this->plantManagerId
            ? Orders::forPlantManager($this->plantManagerId)
            : Orders::forVendor($this->vendorId, $isAdmin, $type);

        $thisMonthOrders = (clone $baseQuery)->whereBetween('created_at', [$dates['startOfThisMonth'], $dates['endOfThisMonth']])->count();
        $lastMonthOrders = (clone $baseQuery)->whereBetween('created_at', [$dates['startOfLastMonth'], $dates['endOfLastMonth']])->count();
        $thisMonthPendingOrders = (clone $baseQuery)->whereBetween('created_at', [$dates['startOfThisMonth'], $dates['endOfThisMonth']])->where('status', 'pending')->count();
        $thisMonthCompletedOrders = (clone $baseQuery)->whereBetween('created_at', [$dates['startOfThisMonth'], $dates['endOfThisMonth']])->where('status', 'completed')->count();
        $thisMonthInProgressOrders = (clone $baseQuery)->whereBetween('created_at', [$dates['startOfThisMonth'], $dates['endOfThisMonth']])->where('status', 'in-progress')->count();
        $todayOrders = (clone $baseQuery)->whereDate('created_at', $dates['today'])->count();
        $yesterdayPendingOrders = (clone $baseQuery)->whereDate('created_at', $dates['yesterday'])->where('status', 'pending')->count();
        $allPendingOrdersCount = (clone $baseQuery)->whereDate('created_at', '!=', $dates['today'])->where('status', 'pending')->orderBy('created_at', 'desc')->count();
        $todayPendingOrders = (clone $baseQuery)->whereDate('created_at', $dates['today'])->where('status', 'pending')->count();
        $todayCompletedOrders = (clone $baseQuery)->whereDate('created_at', $dates['today'])->where('status', 'completed')->count();
        $todayInProgressOrders = (clone $baseQuery)->whereDate('created_at', $dates['today'])->where('status', 'in-progress')->count();

        $thisMonthCustomers = $this->getCustomersCount($dates['startOfThisMonth'], $dates['endOfThisMonth'], $isAdmin, $type);
        $lastMonthCustomers = $this->getCustomersCount($dates['startOfLastMonth'], $dates['endOfLastMonth'], $isAdmin, $type);

        $orderChange = $this->percentChange($thisMonthOrders, $lastMonthOrders);
        $customerChange = $this->percentChange($thisMonthCustomers, $lastMonthCustomers);

        $savvyPlant = Plant::whereIn('name', ['warje', 'Manjri'])->get();
        $plantWiseStats = [];

        foreach ($savvyPlant as $plant) {
            $plantQuery = (clone $baseQuery)
            ->whereHas('shipping', function ($q) use ($plant) {
                $q->where('plant_id', $plant->id);
            });

            $thisMonthOrders = (clone $plantQuery)
                ->whereBetween('created_at', [$dates['startOfThisMonth'], $dates['endOfThisMonth']])
                ->count();

            $lastMonthOrders = (clone $plantQuery)
                ->whereBetween('created_at', [$dates['startOfLastMonth'], $dates['endOfLastMonth']])
                ->count();

            $thisMonthPendingOrders = (clone $plantQuery)
                ->whereBetween('created_at', [$dates['startOfThisMonth'], $dates['endOfThisMonth']])
                ->where('status', 'pending')
                ->count();

            $thisMonthCompletedOrders = (clone $plantQuery)
                ->whereBetween('created_at', [$dates['startOfThisMonth'], $dates['endOfThisMonth']])
                ->where('status', 'completed')
                ->count();

            $thisMonthInProgressOrders = (clone $plantQuery)
                ->whereBetween('created_at', [$dates['startOfThisMonth'], $dates['endOfThisMonth']])
                ->where('status', 'in-progress')
                ->count();

            $todayOrders = (clone $plantQuery)
                ->whereDate('created_at', $dates['today'])
                ->count();

            $yesterdayPendingOrders = (clone $plantQuery)
                ->whereDate('created_at', $dates['yesterday'])
                ->where('status', 'pending')
                ->count();

            $allPendingOrdersCount = (clone $plantQuery)
                ->whereDate('created_at', '!=', $dates['today'])
                ->where('status', 'pending')
                ->count();

            $todayPendingOrders = (clone $plantQuery)
                ->whereDate('created_at', $dates['today'])
                ->where('status', 'pending')
                ->count();

            $todayCompletedOrders = (clone $plantQuery)
                ->whereDate('created_at', $dates['today'])
                ->where('status', 'completed')
                ->count();

            $todayInProgressOrders = (clone $plantQuery)
                ->whereDate('created_at', $dates['today'])
                ->where('status', 'in-progress')
                ->count();


            $orderChange = $this->percentChange($thisMonthOrders, $lastMonthOrders);

            $plantWiseStats[$plant->name] = [
                'thisMonthOrders'        => $thisMonthOrders,
                'thisMonthPendingOrders' => $thisMonthPendingOrders,
                'thisMonthCompletedOrders'     => $thisMonthCompletedOrders,
                'thisMonthInProgressOrders'    => $thisMonthInProgressOrders,
                'todayOrders'            => $todayOrders,
                'yesterdayPendingOrders'       => $yesterdayPendingOrders,
                'allPendingOrdersCount'             => $allPendingOrdersCount,
                'todayPendingOrders'           => $todayPendingOrders,
                'todayCompletedOrders'         => $todayCompletedOrders,
                'todayInProgressOrders'        => $todayInProgressOrders,
                'orderChange'            => $orderChange,
            ];
        }

        $rawMaterialStock = collect([
            'labels' => collect(),
            'caps' => collect(),
            'jars' => collect(),
            'maintenance' => collect([
                [
                    'name' => 'Green Jar',
                    'quantity' => (float) JarMaintance::where('type', 'green-jar')->sum('qty'),
                ],
                [
                    'name' => 'Leaked Jar',
                    'quantity' => (float) JarMaintance::where('type', 'leacked-jar')->sum('qty'),
                ],
            ]),
        ]);

        if ($selectedPlantId) {
            $plantStock = RawStockForPlant::query()
                ->where('plant_id', $selectedPlantId)
                ->whereHas('rawMaterialVariant.rawMaterial', fn($query) =>
                    $query->whereIn('name', ['Label', 'Cap', 'Jar'])
                )
                ->with('rawMaterialVariant.rawMaterial')
                ->get();

            $rawMaterialStock = collect([
                'labels' => $plantStock
                    ->filter(fn($stock) => $stock->rawMaterialVariant?->rawMaterial?->name === 'Label')
                    ->map(fn($stock) => [
                        'name' => $stock->rawMaterialVariant?->variant_name ?? '-',
                        'quantity' => (float) $stock->total_quantity,
                    ])->values(),
                'caps' => $plantStock
                    ->filter(fn($stock) => $stock->rawMaterialVariant?->rawMaterial?->name === 'Cap')
                    ->map(fn($stock) => [
                        'name' => $stock->rawMaterialVariant?->variant_name ?? '-',
                        'quantity' => (float) $stock->total_quantity,
                    ])->values(),
                'jars' => $plantStock
                    ->filter(fn($stock) => $stock->rawMaterialVariant?->rawMaterial?->name === 'Jar')
                    ->map(function ($stock) {
                        $variantName = $stock->rawMaterialVariant?->variant_name ?? '-';

                        return [
                            'name' => $variantName === 'without Label'
                                ? 'Empty Jar'
                                : (str_starts_with($variantName, 'with Label - ')
                                    ? 'Jar with ' . str_replace('with Label - ', '', $variantName) . ' Label'
                                    : $variantName),
                            // Keep this identical to getRawStock(), which reports
                            // the plant's current raw stock from total_quantity.
                            'quantity' => (float) $stock->total_quantity,
                        ];
                    })->values(),
                'maintenance' => collect([
                    [
                        'name' => 'Green Jar',
                        'quantity' => (float) JarMaintance::where('type', 'green-jar')->sum('qty'),
                    ],
                    [
                        'name' => 'Leaked Jar',
                        'quantity' => (float) JarMaintance::where('type', 'leacked-jar')->sum('qty'),
                    ],
                ]),
            ]);
        }


        list($ordersCountByPlant, $plants) = $this->getPlantOrderData($isAdmin, $type);

        foreach ($plants as $plantId => $plantName) {
            $ordersCountByPlant[$plantId] = $ordersCountByPlant[$plantId] ?? 0;
        }

        if ($this->plantManagerId || $this->vendorId) {
            $data = compact(
                'thisMonthOrders', 'thisMonthPendingOrders','thisMonthCompletedOrders', 'thisMonthInProgressOrders', 'todayOrders', 'todayPendingOrders', 'allPendingOrdersCount',
                'yesterdayPendingOrders', 'todayCompletedOrders', 'todayInProgressOrders',
                'orderChange', 'customerChange', 'thisMonthCustomers',
                 'ordersCountByPlant', 'plants'
            );
        } else {
            $record = $this->getOrdersSummary($dates, $isAdmin);
            $data = compact('record', 'allPendingOrdersCount', 'ordersCountByPlant', 'plants', 'plantWiseStats');
        }

        $data = array_merge($data, compact(
            'dashboardPlants',
            'selectedPlant',
            'rawMaterialStock'
        ));

        return  view('home', $data);
    }

    public function yesterdayPendingOrdersData(Request $request)
    {
        $statusClasses = [
            'cancelled' => 'bg-danger-subtle text-danger',
            'pending' => 'bg-warning-subtle text-warning',
            'completed' => 'bg-success-subtle text-success',
            'in_progress' => 'bg-info-subtle text-info',
        ];

        $dates = $this->getDateRanges();
        $userRole = auth()->user()->role;
        $isAdmin = ($userRole === 'admin');
        $type = $request->query('value', 'all');

        $baseQuery = $this->plantManagerId
            ? Orders::forPlantManager($this->plantManagerId)
            : Orders::forVendor($this->vendorId, $isAdmin, $type);

        $user = auth()->user();

        $query = (clone $baseQuery)
            ->whereDate('created_at', '!=', $dates['today'])
            ->where('status', 'pending')
            ->with(['customers', 'shipping', 'drivers']) // eager load relationships
            ->orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->addColumn('order_id', function ($order) use ($user) {
                $icon1 = '';
                $icon2 = '';

                if ($user?->vendor?->id === null && $order->drivers?->vendor_id != null && $user?->plantManager?->id == null) {
                    $icon1 = '<i class="ri-user-shared-line"></i>';
                }
                if ($order->type == 'additional') {
                    $icon2 = '<i class="ri-shopping-cart-line"></i>';
                }

                return '<a href="'.url('order/' . $order->id).'" class="fw-medium link-primary">#'.$order->id.' '.$icon1.' '.$icon2.'</a>';
            })
            ->addColumn('customer', function ($order) {
                return '<div class="d-flex align-items-center"><div class="flex-grow-1"><span style="white-space: pre-wrap;">'.$order->customers->name.'</span></div></div>';
            })
            ->addColumn('shipping_address', function ($order) {
                return '<div class="d-flex align-items-center"><div class="flex-grow-1"><span style="white-space: pre-wrap;">'.$order->shipping->shipping_address.'</span></div></div>';
            })
            ->addColumn('driver', fn($order) => $order->drivers?->name ?? '')
            ->addColumn('delivery_quantity', fn($order) => $order->develivered_qty)
            ->addColumn('status', function ($order) use ($statusClasses) {
                $class = $statusClasses[$order->status] ?? 'bg-secondary-subtle text-secondary';
                $text = ucfirst(str_replace('_', ' ', $order->status));
                return '<span class="badge '.$class.' p-2">'.$text.'</span>';
            })
            ->addColumn('date', fn($order) => $order->created_at->format('d-m-Y'))

            // 🔍 Make related fields searchable
            ->filterColumn('customer', function($query, $keyword) {
                $query->whereHas('customers', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('shipping_address', function($query, $keyword) {
                $query->whereHas('shipping', function ($q) use ($keyword) {
                    $q->where('shipping_address', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('driver', function($query, $keyword) {
                $query->whereHas('drivers', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })

            ->rawColumns(['order_id', 'customer', 'shipping_address', 'status']) // allow HTML
            ->make(true);
    }

    private function getDateRanges()
    {
        return [
            'startOfThisMonth' => Carbon::now()->startOfMonth(),
            'endOfThisMonth' => Carbon::now()->endOfMonth(),
            'startOfLastMonth' => Carbon::now()->subMonth()->startOfMonth(),
            'endOfLastMonth' => Carbon::now()->subMonth()->endOfMonth(),
            'today' => Carbon::today(),
            'yesterday' => Carbon::yesterday(),
        ];
    }

    private function percentChange($current, $previous)
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function getCustomersCount($start, $end, $isAdmin, $type)
    {
        $query = Customers::whereBetween('created_at', [$start, $end]);

        if ($this->plantManagerId) {
            $query->whereHas('shippingAddresses', fn($q) =>
                $q->where('plant_id', $this->plantManagerId)
            );
        } elseif ($isAdmin) {
            $query->whereHas('shippingAddresses', function ($q) use ($type) {
                if ($type === 'pan_india') {
                    $q->whereNotNull('vendor_id');
                } elseif ($type === 'local') {
                    $q->whereNull('vendor_id');
                }
            });
        } elseif ($this->vendorId) {
            $query->whereHas('shippingAddresses', fn($q) =>
                $q->where('vendor_id', $this->vendorId)
            );
        }

        return $query->count();
    }

    private function getPlantOrderData($isAdmin, $type)
    {
        $orders = Orders::whereHas('route', function ($query) use ($isAdmin, $type) {
            if ($this->plantManagerId) {
                $query->where('plant_id', $this->plantManagerId);
            } elseif ($isAdmin) {
                if ($type === 'pan_india') {
                    $query->whereNotNull('vendor_id');
                } elseif ($type === 'local') {
                    $query->whereNull('vendor_id');
                }
            } elseif ($this->vendorId) {
                $query->where('vendor_id', $this->vendorId);
            } else {
                $query->whereNull('vendor_id');
            }
        })->with('route')->get();

        $ordersCountByPlant = $orders->groupBy(fn($order) => optional($order->route)->plant_id)->map->count();

        $plants = Plant::when($this->plantManagerId, fn($q) =>
                $q->where('id', $this->plantManagerId)
            )
            ->when(!$this->plantManagerId && $isAdmin, function ($query) use ($type) {
                if ($type === 'pan_india') $query->whereNotNull('vendor_id');
                elseif ($type === 'local') $query->whereNull('vendor_id');
            })
            ->when(!$isAdmin && $this->vendorId, fn($q) => $q->where('vendor_id', $this->vendorId))
            ->pluck('name', 'id');

        return [$ordersCountByPlant, $plants];
    }

    private function getOrdersSummary($dates, $isAdmin)
    {
        $summary = [];
        foreach (['all', 'local', 'pan_india'] as $type) {
            $baseQuery = Orders::forVendor($this->vendorId, $isAdmin, $type);

            $data = [
                'thisMonthOrders' => (clone $baseQuery)->whereBetween('created_at', [$dates['startOfThisMonth'], $dates['endOfThisMonth']])->count(),
                'thisMonthPendingOrders' => (clone $baseQuery)->whereBetween('created_at', [$dates['startOfThisMonth'], $dates['endOfThisMonth']])->where('status', 'pending')->count(),
                'thisMonthCompletedOrders' => (clone $baseQuery)->whereBetween('created_at', [$dates['startOfThisMonth'], $dates['endOfThisMonth']])->where('status', 'completed')->count(),
                'thisMonthInProgressOrders' => (clone $baseQuery)->whereBetween('created_at', [$dates['startOfThisMonth'], $dates['endOfThisMonth']])->where('status', 'in-progress')->count(),
                'lastMonthOrders' => (clone $baseQuery)->whereBetween('created_at', [$dates['startOfLastMonth'], $dates['endOfLastMonth']])->count(),
                'todayOrders' => (clone $baseQuery)->whereDate('created_at', $dates['today'])->count(),
                'yesterdayPendingOrders' => (clone $baseQuery)->whereDate('created_at', $dates['yesterday'])->where('status', 'pending')->count(),
                'allPendingOrdersCount' => (clone $baseQuery)->whereDate('created_at', '!=', $dates['today'])->where('status', 'pending')->count(),
                'todayPendingOrders' => (clone $baseQuery)->whereDate('created_at', $dates['today'])->where('status', 'pending')->count(),
                'todayCompletedOrders' => (clone $baseQuery)->whereDate('created_at', $dates['today'])->where('status', 'completed')->count(),
                'todayInProgressOrders' => (clone $baseQuery)->whereDate('created_at', $dates['today'])->where('status', 'in-progress')->count(),
            ];

            $thisMonthCustomers = $this->getCustomersCount($dates['startOfThisMonth'], $dates['endOfThisMonth'], $isAdmin, $type);
            $lastMonthCustomers = $this->getCustomersCount($dates['startOfLastMonth'], $dates['endOfLastMonth'], $isAdmin, $type);

            $data['thisMonthCustomers'] = $thisMonthCustomers;
            $data['orderChange'] = $this->percentChange($data['thisMonthOrders'], $data['lastMonthOrders']);
            $data['customerChange'] = $this->percentChange($thisMonthCustomers, $lastMonthCustomers);

            $summary[$type] = $data;
        }

        return $summary;
    }

    public function downloadCardZip(Request $request)
    {
        $customerIdsRaw = $request->input('customer_id', '');
        $customerIds = array_filter(explode(',', $customerIdsRaw));

        $startDate = Carbon::parse($request->input('start_date', now()->format('Y-m-d')))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date', now()->format('Y-m-d')))->endOfDay();

        $cardsQuery = DigitalCard::query()
            ->whereBetween('created_at', [$startDate, $endDate]);

        if (!empty($customerIds)) {
            $cardsQuery->whereHas('order.customers', function ($q) use ($customerIds) {
                $q->whereIn('id', $customerIds);
            });
        }

        $cardsQuery->whereHas('order', function ($q) {
            $q->where(function ($q2) {
                $q2->where('develivered_qty', '!=', 0)
                ->orWhere('return_qty', '!=', 0);
            });
        });

        $cards = $cardsQuery->with([
            'order.customers',
            'order.shipping',
            'order.drivers',
            'acceptBy'
        ])->get();

        if ($cards->isEmpty()) {
            return response()->json(['message' => 'No cards found for the given criteria.'], 404);
        }

        // Create a temp folder
        $folderPath = storage_path('app/tmp/digital_cards_' . now()->timestamp);
        File::makeDirectory($folderPath, 0755, true);

        // Grouping by shipping_id and customer_id to ensure uniqueness
        $cardsGrouped = $cards->groupBy(function ($card) {
            $shippingId = optional($card->order->shipping)->id ?? 'unknown_shipping_id';
            $customerId = optional(optional($card->order->shipping)->Customers)->id ?? 'unknown_customer_id';
            return "{$shippingId}__{$customerId}";
        });

        foreach ($cardsGrouped as $groupKey => $cardsGroup) {
            [$shippingId, $customerId] = explode('__', $groupKey);

            $firstCard = $cardsGroup->first();
            $shipping = optional($firstCard->order->shipping);
            $customer = optional($shipping->Customers);

            $shippingName = $shipping->shipping_address ?? 'unknown_shipping';
            $customerName = $customer->name ?? 'unknown_customer';
            $customerZohiId = $customer->customer_zohi_id ?? 'unknown_zoho';

            $pdf = PDF::loadView('pdf.delivery_card', [
                'cards' => collect($cardsGroup)->sortBy('created_at')->values(),
                'shipping_name' => $shippingName,
                'customer_name' => $customerName,
                'customer_zohi_id' => $customerZohiId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ])->setPaper('a4', 'portrait');

            $shortShippingName = Str::slug($shippingName);
            $shortCustomerName = Str::slug($customerName);
            $formattedEndDate = $endDate->format('d_m_y');

            $fileName = Str::slug("DC_{$shortShippingName}_{$shortCustomerName}_{$formattedEndDate}") . '.pdf';

            file_put_contents("{$folderPath}/{$fileName}", $pdf->output());
        }

        // Create ZIP
        $zipName = 'digital_cards_' . now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path("app/public/{$zipName}");

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach (File::files($folderPath) as $file) {
                $zip->addFile($file->getRealPath(), $file->getFilename());
            }
            $zip->close();
        } else {
            File::deleteDirectory($folderPath);
            return response()->json(['message' => 'Failed to create zip archive.'], 500);
        }

        File::deleteDirectory($folderPath);

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }


}
