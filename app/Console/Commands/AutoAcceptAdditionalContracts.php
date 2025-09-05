<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contracts;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class AutoAcceptAdditionalContracts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-accept-additional-contracts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-accept contracts 1 hour after creation if still pending';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $oneHourAgo = $now->copy()->subHour();
        $tomorrow = $now->copy()->addDay();

        // ✅ Log start of scheduler
        Log::channel('scheduler')->info("🟢 START Auto-accept contracts job at {$now}");

        // Fetch contracts to auto-accept
        $contracts = Contracts::where('type', 'addition')
            ->whereBetween('date', [$now->toDateString(), $tomorrow->toDateString()])
            ->where('accepted_status', 'pending')
            ->where('status', 'active')
            ->where('created_at', '<=', $oneHourAgo)
            ->get();

        foreach ($contracts as $contract) {
            $contract->accepted_status = 'accepted';
            $contract->save();

            Log::channel('scheduler')->info("✅ Auto-accepted contract ID {$contract->id} at {$now}");
        }

        // Run generate-contract-orders AFTER processing all contracts
        if ($contracts->isNotEmpty()) {
            Artisan::call('app:generate-contract-orders');
            Log::channel('scheduler')->info("🔄 Triggered: app:generate-contract-orders at {$now}");
        }

        // ✅ Log end of scheduler
        Log::channel('scheduler')->info("🔴 END Auto-accept contracts job at " . \Carbon\Carbon::now());
    }


}
