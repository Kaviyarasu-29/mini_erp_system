<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockLog;
use App\Models\Supplier;
use App\Services\CodeGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        if ($suppliers->isEmpty() || $products->isEmpty()) {
            return;
        }

        $purchasesData = [
            [
                'supplier' => $suppliers->firstWhere('name', 'NextGen Wholesale') ?? $suppliers->first(),
                'purchased_at' => now()->subDays(15)->format('Y-m-d H:i:s'),
                'items' => [
                    ['product_sku' => 'ELEC-PHN-001', 'qty' => 10, 'cost' => 110000.00, 'tax_rate' => 18],
                    ['product_sku' => 'ELEC-PHN-002', 'qty' => 8, 'cost' => 105000.00, 'tax_rate' => 18],
                    ['product_sku' => 'ELEC-ACC-001', 'qty' => 25, 'cost' => 7200.00, 'tax_rate' => 18],
                ],
            ],
            [
                'supplier' => $suppliers->firstWhere('name', 'Apex Distributors') ?? $suppliers->skip(1)->first() ?? $suppliers->first(),
                'purchased_at' => now()->subDays(10)->format('Y-m-d H:i:s'),
                'items' => [
                    ['product_sku' => 'ELEC-LAP-001', 'qty' => 5, 'cost' => 145000.00, 'tax_rate' => 18],
                    ['product_sku' => 'ELEC-LAP-002', 'qty' => 6, 'cost' => 112000.00, 'tax_rate' => 18],
                    ['product_sku' => 'ELEC-AUD-001', 'qty' => 15, 'cost' => 22000.00, 'tax_rate' => 18],
                    ['product_sku' => 'ELEC-WEA-001', 'qty' => 12, 'cost' => 34000.00, 'tax_rate' => 18],
                ],
            ],
            [
                'supplier' => $suppliers->firstWhere('name', 'Heritage Fabrics & Garments') ?? $suppliers->first(),
                'purchased_at' => now()->subDays(7)->format('Y-m-d H:i:s'),
                'items' => [
                    ['product_sku' => 'CLOT-MEN-001', 'qty' => 50, 'cost' => 450.00, 'tax_rate' => 12],
                    ['product_sku' => 'CLOT-MEN-002', 'qty' => 40, 'cost' => 850.00, 'tax_rate' => 12],
                    ['product_sku' => 'CLOT-WOM-001', 'qty' => 30, 'cost' => 500.00, 'tax_rate' => 12],
                ],
            ],
            [
                'supplier' => $suppliers->firstWhere('name', 'AgroPrime Commodities') ?? $suppliers->first(),
                'purchased_at' => now()->subDays(3)->format('Y-m-d H:i:s'),
                'items' => [
                    ['product_sku' => 'GROC-SNK-001', 'qty' => 100, 'cost' => 420.00, 'tax_rate' => 5],
                    ['product_sku' => 'GROC-BEV-001', 'qty' => 80, 'cost' => 180.00, 'tax_rate' => 5],
                ],
            ],
        ];

        foreach ($purchasesData as $data) {
            $exists = Purchase::where('supplier_id', $data['supplier']->id)
                ->where('purchased_at', $data['purchased_at'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::transaction(function () use ($data) {
                $invNum = CodeGeneratorService::generateMonthBasedCode(
                    Purchase::class,
                    'purchase_number',
                    $data['purchased_at'],
                    'purchased_at'
                );

                $subtotal = 0;
                $taxAmount = 0;

                foreach ($data['items'] as $itemData) {
                    $lineSubtotal = $itemData['qty'] * $itemData['cost'];
                    $lineTax = $lineSubtotal * ($itemData['tax_rate'] / 100);
                    $subtotal += $lineSubtotal;
                    $taxAmount += $lineTax;
                }

                $purchase = Purchase::create([
                    'supplier_id' => $data['supplier']->id,
                    'purchase_number' => $invNum,
                    'purchased_at' => $data['purchased_at'],
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $subtotal + $taxAmount,
                ]);

                foreach ($data['items'] as $itemData) {
                    $product = Product::where('sku', $itemData['product_sku'])->first();
                    if (! $product) {
                        continue;
                    }

                    $qty = $itemData['qty'];
                    $cost = $itemData['cost'];
                    $rate = $itemData['tax_rate'];
                    $lineSubtotal = $qty * $cost;
                    $lineTax = $lineSubtotal * ($rate / 100);
                    $lineTotal = $lineSubtotal + $lineTax;

                    $barcode = CodeGeneratorService::generateBarcode(PurchaseItem::class, 'barcode', 'BC', 3);

                    $purchaseItem = PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $product->id,
                        'barcode' => $barcode,
                        'quantity' => $qty,
                        'stock_quantity' => $qty,
                        'unit_cost' => $cost,
                        'tax_rate' => $rate,
                        'tax_amount' => $lineTax,
                        'line_total' => $lineTotal,
                    ]);

                    $product->increment('stock_quantity', $qty);

                    StockLog::create([
                        'product_id' => $product->id,
                        'area' => 'purchase',
                        'mode' => '+',
                        'quantity' => $qty,
                        'ref_1' => $purchase->id,
                        'ref_2' => $purchaseItem->id,
                        'ref_3' => $purchase->supplier_id,
                    ]);
                }
            });
        }
    }
}
