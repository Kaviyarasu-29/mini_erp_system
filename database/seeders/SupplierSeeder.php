<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Apex Distributors',
                'email' => 'sales@apexdistributors.com',
                'phone' => '9888777666',
                'address' => 'Industrial Area, Phase 1, New Delhi',
                'is_active' => true,
            ],
            [
                'name' => 'NextGen Wholesale',
                'email' => 'order@nextgenwholesale.com',
                'phone' => '9777666555',
                'address' => 'Electronic City, Bengaluru, Karnataka',
                'is_active' => true,
            ],
            [
                'name' => 'Supreme Supplies',
                'email' => 'info@supremesupplies.in',
                'phone' => '9666555444',
                'address' => 'GIDC Estate, Ahmedabad, Gujarat',
                'is_active' => true,
            ],
            [
                'name' => 'Fortune Logistics & Distribution',
                'email' => 'support@fortunedist.com',
                'phone' => '9555444333',
                'address' => 'Bhiwandi Warehousing Hub, Mumbai, Maharashtra',
                'is_active' => true,
            ],
            [
                'name' => 'Zenith Tech Wholesalers',
                'email' => 'b2b@zenithtech.com',
                'phone' => '9444333222',
                'address' => 'HITEC City, Hyderabad, Telangana',
                'is_active' => true,
            ],
            [
                'name' => 'Heritage Fabrics & Garments',
                'email' => 'sales@heritagefabrics.com',
                'phone' => '9333222111',
                'address' => 'Tirupur Textile Hub, Tamil Nadu',
                'is_active' => true,
            ],
            [
                'name' => 'AgroPrime Commodities',
                'email' => 'contact@agroprime.in',
                'phone' => '9222111000',
                'address' => 'APMC Market, Vashi, Navi Mumbai, Maharashtra',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(
                ['name' => $supplier['name']],
                $supplier
            );
        }
    }
}
