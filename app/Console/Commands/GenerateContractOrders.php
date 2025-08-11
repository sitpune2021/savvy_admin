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
    protected $description = 'Generate orders based on active contract frequency (daily, alternate day, weekly, monthly)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::channel('scheduler')->info('📦 Scheduler command started at: ' . now());
        $today = Carbon::today();

        // Regular Contracts
        $contracts = Contracts::where('status', 'active')->where('type', 'contracts')->get();
        Log::channel('scheduler')->info("Found {$contracts->count()} regular active contracts");

        foreach ($contracts as $contract) {
            $contract->refresh();
            Log::channel('scheduler')->info("Processing contract ID: {$contract->id}");

            $startDate = Carbon::parse($contract->created_at);
            $endDate = (clone $startDate)->add($contract->duration, $contract->duration_type);

            Log::channel('scheduler')->info("Contract ID {$contract->id} | Start: {$startDate}, End: {$endDate}, Today: {$today}");

            if ($today->greaterThan($endDate)) {
                $contract->status = 'expired';
                $contract->save();
                Log::channel('scheduler')->info("❌ Contract ID {$contract->id} expired and updated.");
                continue;
            }

            switch ($contract->frequency) {
                case 'daily':
                    Log::channel('scheduler')->info("Contract ID {$contract->id} frequency: daily");
                    $this->createOrderIfNotExists($contract, $today);
                    break;

                case 'alternate_day':
                    Log::channel('scheduler')->info("Contract ID {$contract->id} frequency: alternate_day");
                    $this->handleAlternateDay($contract, $today);
                    break;

                case 'weekly':
                    Log::channel('scheduler')->info("Contract ID {$contract->id} frequency: weekly");
                    $this->handleWeekly($contract, $today);
                    break;

                case 'monthly':
                    Log::channel('scheduler')->info("Contract ID {$contract->id} frequency: monthly");
                    $this->handleMonthly($contract, $today);
                    break;
            }
        }

        // Additional Contracts
        $contractsAdditional = Contracts::whereIn('status', ['active', 'in-progress'])
            ->where('type', 'additional')
            ->with('sender.shippingAddress')
            ->get();

        Log::channel('scheduler')->info("Found {$contractsAdditional->count()} additional contracts");

        foreach ($contractsAdditional as $contractAdditional) {
            $contractAdditional->refresh();

            if (!$contractAdditional->sender || !$contractAdditional->shippingAddress) {
                Log::warning("⚠️ Skipped additional contract ID {$contractAdditional->id} due to missing sender or shipping address.");
                continue;
            }

            $endDate = Carbon::parse($contractAdditional->date);
            Log::channel('scheduler')->info("Additional contract ID {$contractAdditional->id} end date: {$endDate}");

            if ($today->greaterThan($endDate)) {
                $contractAdditional->status = 'expired';
                $contractAdditional->save();
                Log::channel('scheduler')->info("❌ Additional contract ID {$contractAdditional->id} expired.");
                continue;
            }

            $alreadyInProgress = $contractAdditional->status == 'in-progress';

            if (!$alreadyInProgress && $contractAdditional->accepted_status == 'accepted') {
                $shipping = $contractAdditional->shippingAddress;

                if ($shipping->route_id && $shipping->driver_id) {
                    Orders::create([
                        'customer_id' => $contractAdditional->customer_id,
                        'contract_id' => $contractAdditional->id,
                        'shipping_id' => $shipping->id,
                        'route_id' => $shipping->route_id,
                        'driver_id' => $shipping->driver_id,
                        'status' => 'pending',
                        'type' => 'additional',
                    ]);

                    $contractAdditional->status = 'in-progress';
                    $contractAdditional->save();

                    Log::channel('scheduler')->info("✅ Created order for additional contract ID {$contractAdditional->id}");
                } else {
                    Log::warning("⚠️ Skipped additional contract ID {$contractAdditional->id} due to missing route/driver.");
                }
            } else {
                Log::channel('scheduler')->info("ℹ️ No order created for additional contract ID {$contractAdditional->id} (status in-progress or not accepted).");
            }
        }

        Log::channel('scheduler')->info('✅ Scheduler command completed at: ' . now());
        $this->info('Orders generated successfully based on contract frequency.');
    }

    protected function createOrderIfNotExists($contract, Carbon $today)
    {
        $shippings = ShippingAddress::where('customer_id', $contract->customer_id)
            ->where('contract_id', $contract->id)
            ->get();

        if ($shippings->isEmpty()) {
            Log::warning("⚠️ No shipping addresses found for contract ID {$contract->id}");
            return;
        }

        foreach ($shippings as $shipping) {
            $exists = Orders::whereDate('created_at', $today->toDateString())
                ->where('contract_id', $contract->id)
                ->where('shipping_id', $shipping->id)
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
                    Log::channel('scheduler')->info("✅ Order created for contract ID {$contract->id}, shipping ID {$shipping->id}");
                } else {
                    Log::warning("⚠️ Order not created for contract ID {$contract->id}, shipping ID {$shipping->id} due to missing route/driver.");
                }
            } else {
                Log::channel('scheduler')->info("ℹ️ Order already exists today for contract ID {$contract->id}, shipping ID {$shipping->id}");
            }
        }
    }

    protected function handleAlternateDay($contract, Carbon $today)
    {
        $lastOrder = Orders::where('contract_id', $contract->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastOrder) {
            Log::channel('scheduler')->info("ℹ️ No previous orders for alternate day contract ID {$contract->id}, creating first order.");
            $this->createOrderIfNotExists($contract, $today);
        } elseif (Carbon::parse($lastOrder->created_at)->diffInDays($today) >= 2) {
            Log::channel('scheduler')->info("✅ Alternate day condition met for contract ID {$contract->id}, creating order.");
            $this->createOrderIfNotExists($contract, $today);
        } else {
            Log::channel('scheduler')->info("⏸️ Skipped order for alternate day contract ID {$contract->id} - last order too recent.");
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

            Log::channel('scheduler')->info("📅 Weekly contract ID {$contract->id} | Orders this week: {$weeklyOrderCount}/{$frequencyCount}");

            if ($weeklyOrderCount < $frequencyCount) {
                $this->createOrderIfNotExists($contract, $today);
            } else {
                Log::channel('scheduler')->info("✅ Weekly order limit reached for contract ID {$contract->id}");
            }
        } else {
            Log::channel('scheduler')->info("⏸️ Today ({$todayName}) not in weekly days for contract ID {$contract->id}");
        }
    }

    protected function handleMonthly($contract, Carbon $today)
    {
        $configuredDays = explode('|', $contract->days ?? '');
        $normalizedDays = array_filter(array_map('intval', $configuredDays), fn($day) => $day >= 1 && $day <= 31);

        $todayDay = $today->day;
        $lastDayOfMonth = $today->copy()->endOfMonth()->day;
        $frequencyCount = (int) $contract->frequency_count;

        $validDaysThisMonth = array_filter($normalizedDays, fn($day) => $day <= $lastDayOfMonth);
        $missingDays = array_filter($normalizedDays, fn($day) => $day > $lastDayOfMonth);

        $isRegularOrderDay = in_array($todayDay, $validDaysThisMonth);
        $isFallbackDay = $todayDay === $lastDayOfMonth && !empty($missingDays);

        Log::channel('scheduler')->info("📆 Monthly contract ID {$contract->id} | Today: {$todayDay} | Configured: " . implode(',', $normalizedDays));

        if ($isRegularOrderDay || $isFallbackDay) {
            $monthStart = $today->copy()->startOfMonth();
            $monthEnd = $today->copy()->endOfMonth();

            $monthlyOrderCount = Orders::where('contract_id', $contract->id)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();

            Log::channel('scheduler')->info("📦 Monthly orders count: {$monthlyOrderCount}/{$frequencyCount} for contract ID {$contract->id}");

            if ($monthlyOrderCount < $frequencyCount) {
                $this->createOrderIfNotExists($contract, $today);
            } else {
                Log::channel('scheduler')->info("✅ Monthly order limit reached for contract ID {$contract->id}");
            }
        } else {
            Log::channel('scheduler')->info("⏸️ Today is not a valid order day for monthly contract ID {$contract->id}");
        }
    }

}
