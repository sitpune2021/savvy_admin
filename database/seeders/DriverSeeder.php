<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Drivers;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Drivers::create([
            'name' => 'Acoustic Niru',
            'email' => 'an@gmail.com',
            'phone_no' => '7887565827',
            'full_address' => 'Satara',
            'country' => 'India',
            'state' => 'Maharashtra',
            'city' => 'Satara',
            'pincode' => '415002',
            'license_no' => 'Bank Name',
            'vehicle_no' => 'Branch',
            'vehicle_name' => 'Account Holder Name',
            'pan_card' => 'Account Number',
            'aadhar_card' => 'IFSC',
            'pan_card_FILE' => null,
            'aadhar_card_FILE' => null,
        ]);

        Drivers::create([
            'name' => 'Acoustic Niruss',
            'email' => 'anc@gmail.com',
            'phone_no' => '7887565827',
            'full_address' => 'Satara',
            'country' => 'India',
            'state' => 'Maharashtra',
            'city' => 'Satara',
            'pincode' => '415002',
            'license_no' => 'SBI',
            'vehicle_no' => 'Pune',
            'vehicle_name' => 'NK',
            'pan_card' => '132456879',
            'aadhar_card' => 'IFTSC',
            'pan_card_FILE' => null,
            'aadhar_card_FILE' => null,
        ]);

        Drivers::create([
            'name' => 'Acoustic Niru---',
            'email' => 'ancu@gmail.com',
            'phone_no' => '7887565827',
            'full_address' => 'Satara',
            'country' => 'India',
            'state' => 'Maharashtra',
            'city' => 'Satara',
            'pincode' => '415002',
            'license_no' => 'Bank Name',
            'vehicle_no' => 'Branch',
            'vehicle_name' => 'Account Holder Name',
            'pan_card' => 'Account Number',
            'aadhar_card' => 'IFSC',
            'pan_card_FILE' => null,
            'aadhar_card_FILE' => null,
        ]);

        Drivers::create([
            'name' => 'Driver 1',
            'email' => 'driver@gmail.com',
            'phone_no' => '7887565827',
            'full_address' => 'Satara',
            'country' => 'India',
            'state' => 'Maharashtra',
            'city' => 'Satara',
            'pincode' => '415002',
            'license_no' => 'MHDJGSTH123',
            'vehicle_no' => 'MH11012BU4914',
            'vehicle_name' => 'Verna',
            'pan_card' => 'IRWPK4004F',
            'aadhar_card' => '99874',
            'pan_card_FILE' => null,
            'aadhar_card_FILE' => null,
        ]);

        Drivers::create([
            'name' => 'Acoustic Niru',
            'email' => 'aniru@gmail.com',
            'phone_no' => '7887565827',
            'full_address' => 'Satara',
            'country' => 'India',
            'state' => 'Maharashtra',
            'city' => 'Satara',
            'pincode' => '415002',
            'license_no' => 'License Number',
            'vehicle_no' => 'Vehical Number',
            'vehicle_name' => 'Vehical Name',
            'pan_card' => 'Driver PAN Card',
            'aadhar_card' => 'Aadhar Card Number',
            'pan_card_FILE' => 'public/uploads/driver/4216.jpg',
            'aadhar_card_FILE' => 'public/uploads/driver/6075.png',
        ]);

        Drivers::create([
            'name' => 'Acoustic Niru',
            'email' => 'kk@gmail.com',
            'phone_no' => '8080875684',
            'full_address' => 'Satara',
            'country' => 'India',
            'state' => 'Maharashtra',
            'city' => 'Satara',
            'pincode' => '415002',
            'license_no' => '1',
            'vehicle_no' => '1',
            'vehicle_name' => '1',
            'pan_card' => '1',
            'aadhar_card' => '1',
            'pan_card_FILE' => 'public/uploads/driver/2560.jpg',
            'aadhar_card_FILE' => 'public/uploads/driver/5836.png',
        ]);

        Drivers::create([
            'name' => 'Prasad',
            'email' => 'prasad2706@gmail.com',
            'phone_no' => '7030531445',
            'full_address' => 'Chichwad',
            'country' => 'India',
            'state' => 'MH',
            'city' => 'Pune',
            'pincode' => '411062',
            'license_no' => 'MH1477889',
            'vehicle_no' => 'MH14DW8507',
            'vehicle_name' => 'Appe',
            'pan_card' => 'ALXPJ3019J',
            'aadhar_card' => '117788995522',
            'pan_card_FILE' => 'public/uploads/driver/7513.jpg',
            'aadhar_card_FILE' => 'public/uploads/driver/4122.png',
        ]);

        Drivers::create([
            'name' => 'Dinesh',
            'email' => 'dinesh@savvy.com',
            'phone_no' => '9307527539',
            'full_address' => 'NA',
            'country' => 'NA',
            'state' => 'NA',
            'city' => 'NA',
            'pincode' => 'NA',
            'license_no' => 'MHNPS6703G',
            'vehicle_no' => '8175',
            'vehicle_name' => 'Appe',
            'pan_card' => '8612 4226 2900',
            'aadhar_card' => '8612 4226 2900',
            'pan_card_FILE' => 'public/uploads/driver/1846.png',
            'aadhar_card_FILE' => 'public/uploads/driver/2259.png',
        ]);

        Drivers::create([
            'name' => 'Sham',
            'email' => 'sham@savvy.com',
            'phone_no' => '8523697410',
            'full_address' => 'Sr 78 Pandhari Industrial Estate Shivane',
            'country' => 'India',
            'state' => 'Maharashtra',
            'city' => 'Pune',
            'pincode' => '411023',
            'license_no' => '1234567890',
            'vehicle_no' => '1038',
            'vehicle_name' => 'abc',
            'pan_card' => 'PKJUT5689A',
            'aadhar_card' => '3025 5054 258',
            'pan_card_FILE' => 'public/uploads/driver/804.png',
            'aadhar_card_FILE' => 'public/uploads/driver/3314.png',
        ]);
    }
}
