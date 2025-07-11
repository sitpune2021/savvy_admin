<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rawMaterials = ['Jar', 'Label', 'Cap'];

        foreach ($rawMaterials as $material) {
            \App\Models\rawMaterial::create(['name' => $material]);
        }
    }
}
