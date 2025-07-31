<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
         DB::statement("
             INSERT INTO jar_transportations (plant_id, driver_id, date, status, created_at, updated_at, deleted_at)
            SELECT plant_id, id, CURRENT_DATE, 'dispatching', NOW(), NOW(), deleted_at
            FROM drivers
            WHERE plant_id IS NOT NULL
        ");

        // DB::statement("
        //     INSERT INTO jar_transportations (plant_id, driver_id, date, status, created_at, updated_at, deleted_at)
        //     SELECT plant_id, id, CURRENT_DATE, 'dispatching', NOW(), NOW(), deleted_at
        //     FROM drivers
        //     WHERE plant_id IS NOT NULL
        //     ON DUPLICATE KEY UPDATE 
        //         status = VALUES(status),
        //         updated_at = VALUES(updated_at),
        //         deleted_at = VALUES(deleted_at)
        // ");

        $this->info('Driver transportation records synced.');
    }
}
