<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportLocationData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-location-data';

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
        $response = Http::get('https://api.example.com/countries');
    $countries = $response->json();

    // Find India in the response
    $india = collect($countries)->firstWhere('name', 'India');

    if ($india) {
        $country = Country::create(['name' => $india['name']]);

        foreach ($india['states'] as $stateData) {
            $state = State::create([
                'name' => $stateData['name'],
                'country_id' => $country->id
            ]);

            foreach ($stateData['cities'] as $cityData) {
                $city = City::create([
                    'name' => $cityData['name'],
                    'state_id' => $state->id
                ]);

                foreach ($cityData['pincodes'] as $pincode) {
                    Pincode::create([
                        'code' => $pincode,
                        'city_id' => $city->id
                    ]);
                }
            }
        }

        $this->info('Indian location data imported successfully!');
    } else {
        $this->error('India data not found in the API response.');
    }
    }
}
