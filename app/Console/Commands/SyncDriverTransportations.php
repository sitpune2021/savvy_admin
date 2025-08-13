<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Drivers;
use App\Models\JarTransportation;

class SyncDriverTransportations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-driver-transportations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert or update jar_transportations for drivers with a plant';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $now = now();

        $drivers = Drivers::whereNotNull('plant_id')->get();

        $insertData = $drivers->map(function ($driver) use ($today, $now) {
          $data = Orders::whereDate('created_at', $today)
                ->where('driver_id', $driver->id)
                ->with([
                    'drivers:id,name,plant_id',
                    'contract:id,quantity',
                ])
                ->get();
            return [
                'plant_id' => $driver->plant_id,
                'driver_id' => $driver->id,
                'date' => $today,
                'status' => 'dispatching',
                'deleted_at' => $driver->deleted_at,
            ];
        })->toArray();

        JarTransportation::insert($insertData);

        $this->info('Driver transportation records synced.');
    }
}
