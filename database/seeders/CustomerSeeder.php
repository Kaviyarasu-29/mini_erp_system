<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Walk-in Customer',
                'email' => null,
                'phone' => null,
                'address' => 'Store Front',
                'is_active' => true,
            ],
            [
                'name' => 'Ramesh Kumar',
                'email' => 'ramesh.kumar@example.com',
                'phone' => '9876543210',
                'address' => '123 MG Road, Bengaluru, Karnataka',
                'is_active' => true,
            ],
            [
                'name' => 'Anita Sharma',
                'email' => 'anita.sharma@example.com',
                'phone' => '9812345678',
                'address' => '45 Park Street, Kolkata, West Bengal',
                'is_active' => true,
            ],
            [
                'name' => 'Tech Solutions Pvt Ltd',
                'email' => 'contact@techsolutions.com',
                'phone' => '080-41122334',
                'address' => 'IT Park, Phase 2, Pune, Maharashtra',
                'is_active' => true,
            ],
            [
                'name' => 'Global Traders',
                'email' => 'info@globaltraders.in',
                'phone' => '044-28114455',
                'address' => '88 Anna Salai, Chennai, Tamil Nadu',
                'is_active' => true,
            ],
            [
                'name' => 'Vikram Singh',
                'email' => 'vikram.singh@example.com',
                'phone' => '9988776655',
                'address' => '12 Connaught Place, New Delhi',
                'is_active' => true,
            ],
            [
                'name' => 'Priya Patel',
                'email' => 'priya.patel@example.com',
                'phone' => '9879012345',
                'address' => '56 CG Road, Ahmedabad, Gujarat',
                'is_active' => true,
            ],
            [
                'name' => 'Suresh Reddy',
                'email' => 'suresh.reddy@example.com',
                'phone' => '9440123456',
                'address' => '78 Jubilee Hills, Hyderabad, Telangana',
                'is_active' => true,
            ],
            [
                'name' => 'Meera Nair',
                'email' => 'meera.nair@example.com',
                'phone' => '9447098765',
                'address' => '34 MG Road, Kochi, Kerala',
                'is_active' => true,
            ],
            [
                'name' => 'Apex Retail Outlets',
                'email' => 'purchase@apexretail.com',
                'phone' => '022-67890123',
                'address' => 'Bandra Kurla Complex, Mumbai, Maharashtra',
                'is_active' => true,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(
                ['name' => $customer['name']],
                $customer
            );
        }
    }
}
