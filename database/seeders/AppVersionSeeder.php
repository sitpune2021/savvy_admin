<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AppVersion;

class AppVersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $versions = [
            // 🔹 Vendor
            [
                'for' => 'vendor',
                'platform' => 'android',
                'latest_version' => '1.2.0',
                'min_supported_version' => '1.0.0',
                'store_url' => 'https://play.google.com/store/apps/details?id=vendor.app',
            ],
            [
                'for' => 'vendor',
                'platform' => 'ios',
                'latest_version' => '1.2.0',
                'min_supported_version' => '1.0.0',
                'store_url' => 'https://apps.apple.com/app/vendor-app/id123456789',
            ],

            // 🔹 Customer
            [
                'for' => 'customer',
                'platform' => 'android',
                'latest_version' => '2.5.1',
                'min_supported_version' => '2.0.0',
                'store_url' => 'https://play.google.com/store/apps/details?id=customer.app',
            ],
            [
                'for' => 'customer',
                'platform' => 'ios',
                'latest_version' => '2.5.1',
                'min_supported_version' => '2.0.0',
                'store_url' => 'https://apps.apple.com/app/customer-app/id123456790',
            ],

            // 🔹 Driver
            [
                'for' => 'driver',
                'platform' => 'android',
                'latest_version' => '1.0.5',
                'min_supported_version' => '1.0.3',
                'store_url' => 'https://play.google.com/store/apps/details?id=com.sit.savvy_aqua_delivery',
            ],
 
            [
                'for' => 'driver',
                'platform' => 'ios',
                'latest_version' => '3.1.0',
                'min_supported_version' => '3.0.0',
                'store_url' => 'https://apps.apple.com/app/driver-app/id123456791',
            ],

            // 🔹 Plant
            [
                'for' => 'plant',
                'platform' => 'android',
                'latest_version' => '1.0.5',
                'min_supported_version' => '1.0.3',
                'store_url' => 'https://play.google.com/store/apps/details?id=com.sit.savvy_aqua_plant_manager',
            ],
 
            [
                'for' => 'plant',
                'platform' => 'ios',
                'latest_version' => '1.0.5',
                'min_supported_version' => '1.0.0',
                'store_url' => 'https://apps.apple.com/app/plant-app/id123456792',
            ],
        ];

        foreach ($versions as $version) {
            AppVersion::updateOrCreate(
                [
                    'for' => $version['for'],
                    'platform' => $version['platform'],
                ],
                $version
            );
        }

    }
}
