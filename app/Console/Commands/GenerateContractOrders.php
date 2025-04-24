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
        $contracts = Contracts::where('status', 'active')->get();

        foreach ($contracts as $contract) {
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
        Log::info('Scheduler command completed at: ' . now()); // ✅ ADD THIS LINE
        $this->info('Orders generated successfully based on contract frequency.');
    }

    protected function createOrderIfNotExists($contract, Carbon $today)
    {
        $shipping = ShippingAddress::where('customer_id', $contract->customer_id)
            ->where('contract_id', $contract->id)
            ->first();

        if (!$shipping) return;

        // Check if an order already exists for this contract and date (date-only check)
        $exists = Orders::whereDate('created_at', $today->toDateString())
            ->where('contract_id', $contract->id)
            ->exists();

        if (!$exists) {
            Orders::create([
                'customer_id' => $contract->customer_id,
                'contract_id' => $contract->id,
                'shipping_id' => $shipping->id,
                'route_id' => $shipping->route_id,
                'driver_id' => $shipping->driver_id,
                'status' => 'pending',
                'develivered_qty' => $contract->quantity,
                'return_qty' => 0
            ]);
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
            Log::info('Scheduler command weeklyOrderCount: ' . $weeklyOrderCount, $weeklyOrderCount < $frequencyCount); // ✅ ADD THIS LINE

            if ($weeklyOrderCount < $frequencyCount) {
                $this->createOrderIfNotExists($contract, $today);
            }
        }
    }

    protected function handleMonthly($contract, Carbon $today)
    {
        $days = explode('|', $contract->days ?? '');
        $frequencyCount = (int) $contract->frequency_count;

        $todayDay = (string) $today->day;

        if (in_array($todayDay, $days)) {
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
