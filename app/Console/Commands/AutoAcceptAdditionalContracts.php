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
        Log::channel('scheduler')->info(sprintf(
            '🟢 START Auto-accept | now=%s | tomorrow=%s | oneHourAgo=%s',
            $now->toDateTimeString(),
            $tomorrow->toDateTimeString(),
            $oneHourAgo->toDateTimeString()
        ));

        $contracts = Contracts::query()
            ->where('type', 'additional')
            ->where('accepted_status', 'pending')
            ->where('status', 'active')
            ->where('created_at', '<=', $oneHourAgo)  // only older than 1 hour
            ->whereDate('date', '>=', $now->toDateString())
            ->whereDate('date', '<=', $tomorrow->toDateString())
            ->get();

        Log::channel('scheduler')->info(
            'Contracts found: ' . $contracts->count()
        );
        
        foreach ($contracts as $contract) {
            $contract->update(['accepted_status' => 'accepted']);

            Log::channel('scheduler')->info(
                "✅ Auto-accepted contract ID {$contract->id}"
            );
        }

        // Run generate-contract-orders AFTER processing all contracts
        if ($contracts->isNotEmpty()) {
            Artisan::call('app:generate-contract-orders');
            Log::channel('scheduler')->info('🔄 Triggered: app:generate-contract-orders');
        }

        // ✅ Log end of scheduler
        Log::channel('scheduler')->info('🔴 END Auto-accept contracts job');
    }


}
