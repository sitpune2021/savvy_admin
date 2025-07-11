<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MaterialVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rawMaterialVariants = [
            ['raw_material_id' => 1, 'variant_name' => 'with Label - Saavy Water', ],
            ['raw_material_id' => 1, 'variant_name' => 'with Label - mcDonalds', ],
            ['raw_material_id' => 1, 'variant_name' => 'with Label - Royals'],
            ['raw_material_id' => 1, 'variant_name' => 'without Label'],

            ['raw_material_id' => 2, 'variant_name' => 'Saavy Water'],
            ['raw_material_id' => 2, 'variant_name' => 'mcDonalds'],
            ['raw_material_id' => 2, 'variant_name' => 'Royals'],

            ['raw_material_id' => 3, 'variant_name' => 'Plastic Cap'],
        ];

        
        foreach ($rawMaterialVariants as $variant) {
            \App\Models\rawMaterialVariants::create($variant);
        }
    }
}
