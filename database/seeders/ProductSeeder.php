<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tax;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catElectronics = Category::where('name', 'Electronics')->first();
        $subMobile = Category::where('name', 'Mobile Phones')->first();
        $subLaptop = Category::where('name', 'Laptops & Computers')->first();
        $subAccessories = Category::where('name', 'Accessories')->first();
        $subAudio = Category::where('name', 'Audio & Headphones')->first();
        $subWearables = Category::where('name', 'Wearables')->first();

        $catClothing = Category::where('name', 'Clothing & Fashion')->first();
        $subMens = Category::where('name', 'Men\'s Wear')->first();
        $subWomens = Category::where('name', 'Women\'s Wear')->first();

        $catGrocery = Category::where('name', 'Grocery & Staples')->first();
        $subBeverages = Category::where('name', 'Beverages')->first();
        $subSnacks = Category::where('name', 'Snacks & Packaged Food')->first();

        $catHome = Category::where('name', 'Home & Kitchen')->first();
        $subKitchen = Category::where('name', 'Kitchen Appliances')->first();

        $pcs = Unit::where('short_name', 'pcs')->first();
        $box = Unit::where('short_name', 'box')->first();
        $pkt = Unit::where('short_name', 'pkt')->first();
        $set = Unit::where('short_name', 'set')->first();

        $tax0 = Tax::where('rate', 0)->first();
        $tax5 = Tax::where('rate', 5)->first();
        $tax12 = Tax::where('rate', 12)->first();
        $tax18 = Tax::where('rate', 18)->first();

        $products = [
            [
                'category_id' => $catElectronics?->id,
                'subcategory_id' => $subMobile?->id,
                'unit_id' => $pcs?->id,
                'tax_id' => $tax18?->id,
                'name' => 'iPhone 15 Pro (128GB - Natural Titanium)',
                'sku' => 'ELEC-PHN-001',
                'purchase_price' => 110000.00,
                'selling_price' => 134900.00,
                'is_active' => true,
            ],
            [
                'category_id' => $catElectronics?->id,
                'subcategory_id' => $subMobile?->id,
                'unit_id' => $pcs?->id,
                'tax_id' => $tax18?->id,
                'name' => 'Samsung Galaxy S24 Ultra (512GB)',
                'sku' => 'ELEC-PHN-002',
                'purchase_price' => 105000.00,
                'selling_price' => 129999.00,
                'is_active' => true,
            ],
            [
                'category_id' => $catElectronics?->id,
                'subcategory_id' => $subLaptop?->id,
                'unit_id' => $pcs?->id,
                'tax_id' => $tax18?->id,
                'name' => 'Dell XPS 15 Laptop (Intel i9, 32GB RAM)',
                'sku' => 'ELEC-LAP-001',
                'purchase_price' => 145000.00,
                'selling_price' => 169990.00,
                'is_active' => true,
            ],
            [
                'category_id' => $catElectronics?->id,
                'subcategory_id' => $subLaptop?->id,
                'unit_id' => $pcs?->id,
                'tax_id' => $tax18?->id,
                'name' => 'Apple MacBook Air M3 (16GB RAM, 512GB SSD)',
                'sku' => 'ELEC-LAP-002',
                'purchase_price' => 112000.00,
                'selling_price' => 134900.00,
                'is_active' => true,
            ],
            [
                'category_id' => $catElectronics?->id,
                'subcategory_id' => $subAccessories?->id,
                'unit_id' => $pcs?->id,
                'tax_id' => $tax18?->id,
                'name' => 'Logitech MX Master 3S Wireless Mouse',
                'sku' => 'ELEC-ACC-001',
                'purchase_price' => 7200.00,
                'selling_price' => 9995.00,
                'is_active' => true,
            ],
            [
                'category_id' => $catElectronics?->id,
                'subcategory_id' => $subAudio?->id,
                'unit_id' => $pcs?->id,
                'tax_id' => $tax18?->id,
                'name' => 'Sony WH-1000XM5 Wireless Headphones',
                'sku' => 'ELEC-AUD-001',
                'purchase_price' => 22000.00,
                'selling_price' => 29990.00,
                'is_active' => true,
            ],
            [
                'category_id' => $catElectronics?->id,
                'subcategory_id' => $subWearables?->id,
                'unit_id' => $pcs?->id,
                'tax_id' => $tax18?->id,
                'name' => 'Apple Watch Series 9 GPS 45mm',
                'sku' => 'ELEC-WEA-001',
                'purchase_price' => 34000.00,
                'selling_price' => 41900.00,
                'is_active' => true,
            ],
            [
                'category_id' => $catClothing?->id,
                'subcategory_id' => $subMens?->id,
                'unit_id' => $pcs?->id,
                'tax_id' => $tax12?->id,
                'name' => 'Men\'s Slim Fit Cotton Polo T-Shirt',
                'sku' => 'CLOT-MEN-001',
                'purchase_price' => 450.00,
                'selling_price' => 899.00,
                'is_active' => true,
            ],
            [
                'category_id' => $catClothing?->id,
                'subcategory_id' => $subMens?->id,
                'unit_id' => $pcs?->id,
                'tax_id' => $tax12?->id,
                'name' => 'Men\'s Casual Denim Jeans',
                'sku' => 'CLOT-MEN-002',
                'purchase_price' => 850.00,
                'selling_price' => 1799.00,
                'is_active' => true,
            ],
            [
                'category_id' => $catClothing?->id,
                'subcategory_id' => $subWomens?->id,
                'unit_id' => $pcs?->id,
                'tax_id' => $tax12?->id,
                'name' => 'Women\'s Floral Printed Cotton Kurti',
                'sku' => 'CLOT-WOM-001',
                'purchase_price' => 500.00,
                'selling_price' => 1199.00,
                'is_active' => true,
            ],
            [
                'category_id' => $catGrocery?->id,
                'subcategory_id' => $subSnacks?->id,
                'unit_id' => $box?->id,
                'tax_id' => $tax5?->id,
                'name' => 'Premium Royal Basmati Rice (5kg Pack)',
                'sku' => 'GROC-SNK-001',
                'purchase_price' => 420.00,
                'selling_price' => 580.00,
                'is_active' => true,
            ],
            [
                'category_id' => $catGrocery?->id,
                'subcategory_id' => $subBeverages?->id,
                'unit_id' => $pkt?->id,
                'tax_id' => $tax5?->id,
                'name' => 'Organic Darjeeling Green Tea (250g Pouch)',
                'sku' => 'GROC-BEV-001',
                'purchase_price' => 180.00,
                'selling_price' => 299.00,
                'is_active' => true,
            ],
            [
                'category_id' => $catHome?->id,
                'subcategory_id' => $subKitchen?->id,
                'unit_id' => $set?->id,
                'tax_id' => $tax18?->id,
                'name' => 'Philips Air Fryer XL (4.1 Liter)',
                'sku' => 'HOME-KIT-001',
                'purchase_price' => 6800.00,
                'selling_price' => 9495.00,
                'is_active' => true,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::where('sku', $productData['sku'])->first();
            if ($product) {
                $product->update($productData);
            } else {
                $productData['stock_quantity'] = 0;
                Product::create($productData);
            }
        }
    }
}
