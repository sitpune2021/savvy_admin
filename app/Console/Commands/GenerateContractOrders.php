<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        $customers = Customers::all();
        dd($customers);
    }
}
