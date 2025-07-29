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

class HomeController extends BaseController
{
    public function index(Request $request)
    {
        $dates = $this->getDateRanges();
        $userRole = auth()->user()->role;
        $isAdmin = ($userRole === 'admin');
        $type = $request->query('value', 'all');

        $baseQuery = $this->plantManagerId
            ? Orders::forPlantManager($this->plantManagerId)
            : Orders::forVendor($this->vendorId, $isAdmin, $type);

        $thisMonthOrders = (clone $baseQuery)->whereBetween('created_at', [$dates['startOfThisMonth'], $dates['endOfThisMonth']])->count();
        $lastMonthOrders = (clone $baseQuery)->whereBetween('created_at', [$dates['startOfLastMonth'], $dates['endOfLastMonth']])->count();
        $todayOrders = (clone $baseQuery)->whereDate('created_at', $dates['today'])->count();
        $yesterdayPendingOrders = (clone $baseQuery)->whereDate('created_at', $dates['yesterday'])->where('status', 'pending')->count();
        $allPendingOrders = (clone $baseQuery)->whereDate('created_at', '!=', $dates['today'])->where('status', 'pending')->orderBy('created_at', 'desc')->get();
        $todayPendingOrders = (clone $baseQuery)->whereDate('created_at', $dates['today'])->where('status', 'pending')->count();
        $todayCompletedOrders = (clone $baseQuery)->whereDate('created_at', $dates['today'])->where('status', 'completed')->count();
        $todayInProgressOrders = (clone $baseQuery)->whereDate('created_at', $dates['today'])->where('status', 'in-progress')->count();

        $thisMonthCustomers = $this->getCustomersCount($dates['startOfThisMonth'], $dates['endOfThisMonth'], $isAdmin, $type);
        $lastMonthCustomers = $this->getCustomersCount($dates['startOfLastMonth'], $dates['endOfLastMonth'], $isAdmin, $type);

        $orderChange = $this->percentChange($thisMonthOrders, $lastMonthOrders);
        $customerChange = $this->percentChange($thisMonthCustomers, $lastMonthCustomers);

        list($ordersCountByPlant, $plants) = $this->getPlantOrderData($isAdmin, $type);

        foreach ($plants as $plantId => $plantName) {
            $ordersCountByPlant[$plantId] = $ordersCountByPlant[$plantId] ?? 0;
        }

        $allPendingOrdersCount = $allPendingOrders->count();

        if ($this->plantManagerId || $this->vendorId) {
            $data = compact(
                'thisMonthOrders', 'todayOrders', 'todayPendingOrders', 'allPendingOrdersCount',
                'yesterdayPendingOrders', 'todayCompletedOrders', 'todayInProgressOrders',
                'orderChange', 'customerChange', 'thisMonthCustomers',
                'allPendingOrders', 'ordersCountByPlant', 'plants'
            );
        } else {
            $record = $this->getOrdersSummary($dates, $isAdmin);
            $data = compact('record', 'allPendingOrders', 'ordersCountByPlant', 'plants');
        }

        return $request->ajax() ? response()->json($data) : view('home', $data);
    }

    public function fetchPendingOrders(Request $request)
    {
        $dates = $this->getDateRanges();
        $key = $request->get('key');
        $userRole = auth()->user()->role;
        $isAdmin = ($userRole === 'admin');
        $baseQuery = $this->plantManagerId
            ? Orders::forPlantManager($this->plantManagerId)
            : Orders::forVendor($this->vendorId, $isAdmin, $key );

        // Fetch the data based on the key
        $allPendingOrders = (clone $baseQuery)->whereDate('created_at', '!=', $dates['today'])->where('status', 'pending')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'html' => view('components.dashbordTableBody', [
                'allPendingOrders' => $allPendingOrders,
            ])->render()
        ]);
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
                'cards' => collect($cardsGroup),
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
