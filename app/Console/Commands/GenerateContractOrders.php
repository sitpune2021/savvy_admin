<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customers;
use App\Models\ShippingAddress;
use App\Models\Contracts;
use App\Models\Orders;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateContractOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-contract-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Scheduler command started at: ' . now()); // ✅ ADD THIS LINE
        $today = Carbon::today(); // only the date part
        $contracts = Contracts::where('status', 'active')->where('type', 'contracts')->get();


        foreach ($contracts as $contract) {
            $contract->refresh();

            $startDate = Carbon::parse($contract->created_at);
            $endDate = (clone $startDate)->add($contract->duration, $contract->duration_type);

            if ($today->greaterThan($endDate)) {
                $contract->status = 'expired';
                $contract->save();
                continue;
            }

            switch ($contract->frequency) {
                case 'daily':
                    $this->createOrderIfNotExists($contract, $today);
                    break;

                case 'alternate_day':
                    $this->handleAlternateDay($contract, $today);
                    break;

                case 'weekly':
                    $this->handleWeekly($contract, $today);
                    break;

                case 'monthly':
                    $this->handleMonthly($contract, $today);
                    break;
            }
        }

        $contractsAdditional = Contracts::whereIn('status', ['active', 'in-progress'])
        ->where('type', 'additional')
        ->with('sender.shippingAddress')
        ->get();

        Log::info('Total additional contracts found: ' . $contractsAdditional->count());


        foreach ($contractsAdditional as $contractAdditional) {
               $contractAdditional->refresh();
                if (!$contractAdditional->sender || !$contractAdditional->shippingAddress) {
                    Log::warning("Contract ID {$contractAdditional->id} skipped due to missing sender or shipping address.");
                    continue;
                }

            $endDate = Carbon::parse($contractAdditional->date);

            if ($today->greaterThan($endDate)) {
                $contractAdditional->status = 'expired';
                $contractAdditional->save();
                continue;
            }
            $exists = $contractAdditional->status == 'in-progress';
            

            if (!$exists && $contractAdditional->accepted_status == 'accepted') {
                if($contractAdditional->shippingAddress->route_id != null && $contractAdditional->shippingAddress->driver_id != null){
                    Orders::create([
                        'customer_id' => $contractAdditional->customer_id,
                        'contract_id' => $contractAdditional->id,
                        'shipping_id' => $contractAdditional->shippingAddress->id,
                        'route_id' => $contractAdditional->shippingAddress->route_id,
                        'driver_id' => $contractAdditional->shippingAddress->driver_id,
                        'status' => 'pending',
                        'type' => 'additional',
                    ]);
                    $contractAdditional->status = 'in-progress';
                    $contractAdditional->save();

                    Log::info("Order created for additional contract ID {$contractAdditional->id}");

                }
            }
        } 
        
        Log::info('Scheduler command completed at: ' . now()); // ✅ ADD THIS LINE
        $this->info('Orders generated successfully based on contract frequency.');
    }

    protected function createOrderIfNotExists($contract, Carbon $today)
    {
        $shippings = ShippingAddress::where('customer_id', $contract->customer_id)
            ->where('contract_id', $contract->id)
            ->get();

        if ($shippings->isEmpty()) return;

        foreach ($shippings as $shipping) {
            $exists = Orders::whereDate('created_at', $today->toDateString())
                ->where('contract_id', $contract->id)
                ->where('shipping_id', $shipping->id) // Consider per shipping address
                ->exists();

            if (!$exists) {
                if($shipping->route_id != null && $shipping->driver_id != null){
                    Orders::create([
                        'customer_id' => $contract->customer_id,
                        'contract_id' => $contract->id,
                        'shipping_id' => $shipping->id,
                        'route_id' => $shipping->route_id,
                        'driver_id' => $shipping->driver_id,
                        'status' => 'pending',
                    ]);
                }
            }
        }

    }

    protected function handleAlternateDay($contract, Carbon $today)
    {
        $lastOrder = Orders::where('contract_id', $contract->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastOrder || Carbon::parse($lastOrder->created_at)->diffInDays($today) >= 2) {
            $this->createOrderIfNotExists($contract, $today);
        }
    }

    protected function handleWeekly($contract, Carbon $today)
    {
        $days = explode('|', $contract->days ?? '');
        $frequencyCount = (int) $contract->frequency_count;

        $todayName = strtolower($today->format('l'));
        $lowerDays = array_map('strtolower', $days);
        if (in_array($todayName, $lowerDays)) {
            $weekStart = $today->copy()->startOfWeek();
            $weekEnd = $today->copy()->endOfWeek();

            $weeklyOrderCount = Orders::where('contract_id', $contract->id)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();
            Log::info('Scheduler command weeklyOrderCount: ' . $weeklyOrderCount . $weeklyOrderCount < $frequencyCount); // ✅ ADD THIS LINE

            if ($weeklyOrderCount < $frequencyCount) {
                $this->createOrderIfNotExists($contract, $today);
            }
        }
    }

    protected function handleMonthly($contract, Carbon $today)
    {
        $configuredDays = explode('|', $contract->days ?? '');
        $normalizedDays = array_filter(array_map('intval', $configuredDays), function ($day) {
            return $day >= 1 && $day <= 31;
        });

        $todayDay = $today->day;
        $lastDayOfMonth = $today->copy()->endOfMonth()->day;
        $frequencyCount = (int) $contract->frequency_count;

        $validDaysThisMonth = array_filter($normalizedDays, fn($day) => $day <= $lastDayOfMonth);
        $missingDays = array_filter($normalizedDays, fn($day) => $day > $lastDayOfMonth);

        $isRegularOrderDay = in_array($todayDay, $validDaysThisMonth);
        $isFallbackDay = $todayDay === $lastDayOfMonth && !empty($missingDays);

        if ($isRegularOrderDay || $isFallbackDay) {
            $monthStart = $today->copy()->startOfMonth();
            $monthEnd = $today->copy()->endOfMonth();

            $monthlyOrderCount = Orders::where('contract_id', $contract->id)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();

            if ($monthlyOrderCount < $frequencyCount) {
                $this->createOrderIfNotExists($contract, $today);
            }
        }
    }

}
