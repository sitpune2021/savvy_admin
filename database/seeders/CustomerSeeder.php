<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customers;


class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customers::create([
            'name' => 'Acoustic Niru',
            'email' => 'n@gmail.com',
            'phone_no' => '7887565827',
            'billing_address' => 'Satara',
            'billing_country' => 'India',
            'billing_state' => 'Maharashtra',
            'billing_city' => 'Satara',
            'billing_pincode' => '415002',
            'shipping_address' => 'Bank Name',
            'shipping_country' => 'Branch',
            'shipping_state' => 'Account Holder Name',
            'shipping_city' => 'Account Number',
            'shipping_pincode' => 'IFSC',
        ]);

        Customers::create([
            'name' => 'Acoustic Niruss',
            'email' => 'o@gmail.com',
            'phone_no' => '7887565827',
            'billing_address' => 'Satara',
            'billing_country' => 'India',
            'billing_state' => 'Maharashtra',
            'billing_city' => 'Satara',
            'billing_pincode' => '415002',
            'shipping_address' => 'SBI',
            'shipping_country' => 'Pune',
            'shipping_state' => 'NK',
            'shipping_city' => '132456879',
            'shipping_pincode' => 'IFTSC',
        ]);

        Customers::create([
            'name' => 'Acoustic Niru---123',
            'email' => 'p@gmail.com',
            'phone_no' => '7887565827',
            'billing_address' => 'Satara',
            'billing_country' => 'India',
            'billing_state' => 'Maharashtra',
            'billing_city' => 'Satara',
            'billing_pincode' => '415002',
            'shipping_address' => 'Bank Name',
            'shipping_country' => 'Branch',
            'shipping_state' => 'Account Holder Name',
            'shipping_city' => 'Account Number',
            'shipping_pincode' => 'IFSC',
        ]);

        Customers::create([
            'name' => 'ACC Limited',
            'email' => 'test@example.com',
            'phone_no' => '1111111111',
            'billing_address' => 'Sr No 37/1A, Kondhwa Budruk, Tal Haveli, Pune',
            'billing_country' => 'IN',
            'billing_state' => 'MH',
            'billing_city' => 'Pune',
            'billing_pincode' => '411023',
            'shipping_address' => 'NA',
            'shipping_country' => 'NA',
            'shipping_state' => 'NA',
            'shipping_city' => 'NA',
            'shipping_pincode' => 'NA',
        ]);
    }
}
