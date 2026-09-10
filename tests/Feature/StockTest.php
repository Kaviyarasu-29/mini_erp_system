<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\StockLog;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTest extends TestCase
{
    use RefreshDatabase;

    public function test_end_to_end_stock_lifecycle(): void
    {
        // 1. Create Masters
        $supplier = Supplier::create(['name' => 'Supplier X', 'is_active' => true]);
        $customer = Customer::create(['name' => 'Customer Y', 'is_active' => true]);
        $category = Category::create(['name' => 'Hardware']);
        $unit = Unit::create(['name' => 'Pieces', 'short_name' => 'pcs', 'is_active' => true]);
        $tax = Tax::create(['name' => 'GST 18%', 'rate' => 18, 'is_active' => true]);

        $product = Product::create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'tax_id' => $tax->id,
            'name' => 'Power Tool Z',
            'sku' => 'TOOL-Z-01',
            'purchase_price' => 2000,
            'selling_price' => 3000,
            'stock_quantity' => 0,
            'is_active' => true,
        ]);

        $this->assertEquals(0, $product->stock_quantity);

        // 2. Perform Purchase of 15 units
        $this->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'purchased_at' => '2026-09-10',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 15,
                    'unit_cost' => 2000,
                    'tax_rate' => 18,
                ],
            ],
        ]);

        $product->refresh();
        $this->assertEquals(15.000, $product->stock_quantity);

        $purchaseItem = PurchaseItem::where('product_id', $product->id)->first();
        $this->assertNotNull($purchaseItem);
        $this->assertEquals(15.000, $purchaseItem->stock_quantity);
        $this->assertNotEmpty($purchaseItem->barcode);

        $this->assertDatabaseHas('stock_logs', [
            'product_id' => $product->id,
            'area' => 'purchase',
            'mode' => '+',
            'quantity' => 15,
        ]);

        // 3. Perform Sale of 5 units using Purchase Item barcode
        $this->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'sold_at' => '2026-09-10',
            'payment_method' => 'UPI',
            'payment_status' => 'Paid',
            'status' => 'Completed',
            'items' => [
                [
                    'product_id' => $product->id,
                    'barcode' => $purchaseItem->barcode,
                    'quantity' => 5,
                    'unit_price' => 3000,
                    'tax_rate' => 18,
                ],
            ],
        ]);

        $product->refresh();
        $purchaseItem->refresh();

        $this->assertEquals(10.000, $product->stock_quantity);
        $this->assertEquals(10.000, $purchaseItem->stock_quantity);

        $this->assertDatabaseHas('stock_logs', [
            'product_id' => $product->id,
            'area' => 'sale',
            'mode' => '-',
            'quantity' => 5,
        ]);

        // 4. Verify Stock Log total entries
        $logs = StockLog::where('product_id', $product->id)->get();
        $this->assertCount(2, $logs);

        // 5. Delete Sale and verify stock restoration
        $sale = Sale::first();
        $this->delete(route('sales.destroy', $sale));

        $product->refresh();
        $purchaseItem->refresh();

        $this->assertEquals(15.000, $product->stock_quantity);
        $this->assertEquals(15.000, $purchaseItem->stock_quantity);
        $this->assertDatabaseMissing('stock_logs', ['area' => 'sale']);

        // 6. Delete Purchase and verify stock reduction back to zero
        $purchase = Purchase::first();
        $this->delete(route('purchases.destroy', $purchase));

        $product->refresh();
        $this->assertEquals(0.000, $product->stock_quantity);
        $this->assertDatabaseMissing('stock_logs', ['area' => 'purchase']);
    }
}
