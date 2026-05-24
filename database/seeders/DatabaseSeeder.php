<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@housekeeping.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'ashleymayalonzo0000@gmail.com'],
            [
                'name' => 'Ashley May Alonzo',
                'password' => bcrypt('Ashurialonzo0'),
            ]
        );

        $services = [
            ['Normal Cleaning', 'Standard cleaning service for general housekeeping', 'per_sqm', 55.00],
            ['Deep Cleaning', 'Comprehensive deep cleaning of the entire house', 'per_sqm', 75.00],
            ['Sofa Cleaning', 'Professional sofa and upholstery cleaning', 'fixed', 1500.00],
            ['Carpet Cleaning', 'Deep carpet cleaning and stain removal', 'per_sqm', 65.00],
            ['Bathroom Cleaning', 'Thorough bathroom cleaning and sanitization', 'fixed', 800.00],
            ['Kitchen Cleaning', 'Complete kitchen cleaning including appliances', 'fixed', 1000.00],
            ['Window Cleaning', 'Interior and exterior window cleaning', 'fixed', 600.00],
            ['Move-in/Move-out Cleaning', 'Complete house preparation for moving', 'per_sqm', 85.00],
        ];

        foreach ($services as [$name, $description, $pricingType, $basePrice]) {
            Service::updateOrCreate(
                ['service_name' => $name],
                [
                    'description' => $description,
                    'pricing_type' => $pricingType,
                    'base_price' => $basePrice,
                ]
            );
        }

        $employees = [
            ['Maria Santos', '09171234567', 'Head Cleaner'],
            ['Juan Dela Cruz', '09187654321', 'Cleaner'],
            ['Rosa Gonzales', '09195551234', 'Cleaner'],
            ['Pedro Reyes', '09164445678', 'Assistant'],
        ];

        foreach ($employees as [$name, $phone, $position]) {
            Employee::firstOrCreate(
                ['name' => $name],
                [
                    'phone' => $phone,
                    'position' => $position,
                    'status' => 'Active',
                ]
            );
        }
    }
}
