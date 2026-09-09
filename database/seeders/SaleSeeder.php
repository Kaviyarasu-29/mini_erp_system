<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockLog;
use App\Services\CodeGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::where('is_active', true)->get();
        if ($customers->isEmpty()) {
            return;
        }

        $salesData = [
            [
                'customer' => $customers->firstWhere('name', 'Ramesh Kumar') ?? $customers->first(),
                'sold_at' => now()->subDays(5)->format('Y-m-d H:i:s'),
                'payment_method' => 'UPI',
                'payment_status' => 'Paid',
                'status' => 'Completed',
                'items' => [
                    ['product_sku' => 'ELEC-PHN-001', 'qty' => 1, 'unit_price' => 134900.00, 'tax_rate' => 18],
                    ['product_sku' => 'ELEC-ACC-001', 'qty' => 2, 'unit_price' => 9995.00, 'tax_rate' => 18],
                ],
            ],
            [
                'customer' => $customers->firstWhere('name', 'Tech Solutions Pvt Ltd') ?? $customers->skip(1)->first() ?? $customers->first(),
                'sold_at' => now()->subDays(3)->format('Y-m-d H:i:s'),
                'payment_method' => 'Bank Transfer',
                'payment_status' => 'Paid',
                'status' => 'Completed',
                'items' => [
                    ['product_sku' => 'ELEC-LAP-001', 'qty' => 2, 'unit_price' => 169990.00, 'tax_rate' => 18],
                    ['product_sku' => 'ELEC-LAP-002', 'qty' => 1, 'unit_price' => 134900.00, 'tax_rate' => 18],
                    ['product_sku' => 'ELEC-AUD-001', 'qty' => 3, 'unit_price' => 29990.00, 'tax_rate' => 18],
                ],
            ],
            [
                'customer' => $customers->firstWhere('name', 'Anita Sharma') ?? $customers->first(),
                'sold_at' => now()->subDays(2)->format('Y-m-d H:i:s'),
                'payment_method' => 'Credit Card',
                'payment_status' => 'Paid',
                'status' => 'Completed',
                'items' => [
                    ['product_sku' => 'CLOT-MEN-001', 'qty' => 3, 'unit_price' => 899.00, 'tax_rate' => 12],
                    ['product_sku' => 'CLOT-WOM-001', 'qty' => 2, 'unit_price' => 1199.00, 'tax_rate' => 12],
                ],
            ],
            [
                'customer' => $customers->firstWhere('name', 'Walk-in Customer') ?? $customers->first(),
                'sold_at' => now()->subDay()->format('Y-m-d H:i:s'),
                'payment_method' => 'Cash',
                'payment_status' => 'Paid',
                'status' => 'Completed',
                'items' => [
                    ['product_sku' => 'GROC-SNK-001', 'qty' => 5, 'unit_price' => 580.00, 'tax_rate' => 5],
                    ['product_sku' => 'GROC-BEV-001', 'qty' => 4, 'unit_price' => 299.00, 'tax_rate' => 5],
                ],
            ],
        ];

        foreach ($salesData as $data) {
            $exists = Sale::where('customer_id', $data['customer']->id)
                ->where('sold_at', $data['sold_at'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::transaction(function () use ($data) {
                $invNum = CodeGeneratorService::generateMonthBasedCode(
                    Sale::class,
                    'invoice_number',
                    $data['sold_at'],
                    'sold_at'
                );

                $subtotal = 0;
                $taxAmount = 0;

                foreach ($data['items'] as $itemData) {
                    $lineSubtotal = $itemData['qty'] * $itemData['unit_price'];
                    $lineTax = $lineSubtotal * ($itemData['tax_rate'] / 100);
                    $subtotal += $lineSubtotal;
                    $taxAmount += $lineTax;
                }

                $sale = Sale::create([
                    'customer_id' => $data['customer']->id,
                    'invoice_number' => $invNum,
                    'sale_number' => $invNum,
                    'sold_at' => $data['sold_at'],
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $subtotal + $taxAmount,
                    'payment_method' => $data['payment_method'],
                    'payment_status' => $data['payment_status'],
                    'status' => $data['status'],
                ]);

                foreach ($data['items'] as $itemData) {
                    $product = Product::where('sku', $itemData['product_sku'])->first();
                    if (! $product) {
                        continue;
                    }

                    $qty = $itemData['qty'];
                    $price = $itemData['unit_price'];
                    $rate = $itemData['tax_rate'];
                    $lineSubtotal = $qty * $price;
                    $lineTax = $lineSubtotal * ($rate / 100);
                    $lineTotal = $lineSubtotal + $lineTax;

                    $pItem = PurchaseItem::where('product_id', $product->id)
                        ->where('stock_quantity', '>', 0)
                        ->orderBy('id', 'asc')
                        ->first();

                    $barcode = $pItem ? $pItem->barcode : null;

                    $saleItem = SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'barcode' => $barcode,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'tax_rate' => $rate,
                        'tax_amount' => $lineTax,
                        'line_total' => $lineTotal,
                    ]);

                    if ($pItem) {
                        $pItem->decrement('stock_quantity', min($qty, (float) $pItem->stock_quantity));
                    }

                    $product->decrement('stock_quantity', $qty);

                    StockLog::create([
                        'product_id' => $product->id,
                        'area' => 'sale',
                        'mode' => '-',
                        'quantity' => $qty,
                        'ref_1' => $sale->id,
                        'ref_2' => $saleItem->id,
                        'ref_3' => $sale->customer_id,
                    ]);
                }
            });
        }
    }
}
