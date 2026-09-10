<?php

namespace Tests\Feature;

use App\Jobs\SendOrderNotificationJob;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\Unit;
use App\Services\OrderNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    private function createDependencies(): array
    {
        $supplier = Supplier::create(['name' => 'Supplier A', 'is_active' => true]);
        $customer = Customer::create(['name' => 'Customer B', 'email' => 'customerb@example.com', 'is_active' => true]);
        $category = Category::create(['name' => 'General']);
        $unit = Unit::create(['name' => 'Pieces', 'short_name' => 'pcs', 'is_active' => true]);
        $tax = Tax::create(['name' => 'GST 18%', 'rate' => 18, 'is_active' => true]);

        $product = Product::create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'tax_id' => $tax->id,
            'name' => 'Gadget B',
            'sku' => 'GAD-02',
            'purchase_price' => 100,
            'selling_price' => 150,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'purchase_number' => 'SEP001',
            'purchased_at' => now(),
            'subtotal' => 1000,
            'tax_amount' => 180,
            'total_amount' => 1180,
        ]);

        $purchaseItem = PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'barcode' => 'BC001',
            'quantity' => 10,
            'stock_quantity' => 10,
            'unit_cost' => 100,
            'tax_rate' => 18,
            'tax_amount' => 180,
            'line_total' => 1180,
        ]);

        return [$customer, $product, $purchaseItem];
    }

    public function test_can_view_sales_index_page(): void
    {
        [$customer, $product, $purchaseItem] = $this->createDependencies();

        Sale::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'SEP001',
            'sale_number' => 'SEP001',
            'sold_at' => now(),
            'subtotal' => 150,
            'tax_amount' => 27,
            'total_amount' => 177,
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'status' => 'Completed',
        ]);

        $response = $this->get(route('sales.index'));

        $response->assertStatus(200);
        $response->assertSee('SEP001');
        $response->assertSee('Customer B');
    }

    public function test_can_view_create_sale_page_via_new_and_create_routes(): void
    {
        $responseNew = $this->get('/sales/new');
        $responseNew->assertStatus(200);
        $responseNew->assertViewIs('sales.create');

        $responseCreate = $this->get('/sales/create');
        $responseCreate->assertStatus(200);
        $responseCreate->assertViewIs('sales.create');
    }

    public function test_returns_404_for_non_existent_sale(): void
    {
        $response = $this->get('/sales/999999');
        $response->assertStatus(404);
    }

    public function test_can_scan_purchase_item_barcode_via_ajax(): void
    {
        [$customer, $product, $purchaseItem] = $this->createDependencies();

        $response = $this->get(route('sales.scan', 'BC001'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'productId' => $product->id,
                'productName' => 'Gadget B',
                'barcode' => 'BC001',
                'sellingPrice' => 150,
            ],
        ]);
    }

    public function test_returns_404_for_invalid_barcode_scan(): void
    {
        $response = $this->get(route('sales.scan', 'NONEXISTENT'));

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_can_create_sale_and_deduct_stock(): void
    {
        [$customer, $product, $purchaseItem] = $this->createDependencies();

        $response = $this->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'sold_at' => '2026-09-10',
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'status' => 'Pending',
            'items' => [
                [
                    'product_id' => $product->id,
                    'barcode' => 'BC001',
                    'quantity' => 3,
                    'unit_price' => 150,
                    'tax_rate' => 18,
                ],
            ],
        ]);

        $response->assertRedirect(route('sales.index'));

        $this->assertDatabaseHas('sales', [
            'customer_id' => $customer->id,
            'subtotal' => 450,
            'tax_amount' => 81,
            'total_amount' => 531,
        ]);

        $this->assertDatabaseHas('purchase_items', [
            'id' => $purchaseItem->id,
            'stock_quantity' => 7.000,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 7.000,
        ]);

        $this->assertDatabaseHas('stock_logs', [
            'product_id' => $product->id,
            'area' => 'sale',
            'mode' => '-',
            'quantity' => 3,
        ]);
    }

    public function test_dispatches_order_notification_job_when_sale_is_created(): void
    {
        Queue::fake();

        [$customer, $product, $purchaseItem] = $this->createDependencies();

        $response = $this->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'sold_at' => '2026-09-10',
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'status' => 'Completed',
            'items' => [
                [
                    'product_id' => $product->id,
                    'barcode' => 'BC001',
                    'quantity' => 2,
                    'unit_price' => 150,
                    'tax_rate' => 18,
                ],
            ],
        ]);

        $response->assertRedirect(route('sales.index'));

        Queue::assertPushed(SendOrderNotificationJob::class, function (SendOrderNotificationJob $job) use ($customer) {
            return $job->sale->customer_id === $customer->id && $job->event === 'created';
        });

        Queue::assertPushed(SendOrderNotificationJob::class, function (SendOrderNotificationJob $job) use ($customer) {
            return $job->sale->customer_id === $customer->id && $job->event === 'completed';
        });
    }

    public function test_job_logs_formatted_notification_message_when_handled(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function (string $message) {
                return str_contains($message, '[ORDER CREATED]')
                    && str_contains($message, 'INV-TEST-01')
                    && str_contains($message, '354.00');
            });

        [$customer, $product, $purchaseItem] = $this->createDependencies();

        $sale = Sale::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-TEST-01',
            'sale_number' => 'INV-TEST-01',
            'sold_at' => now(),
            'subtotal' => 300,
            'tax_amount' => 54,
            'total_amount' => 354,
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'status' => 'Completed',
        ]);

        $message = OrderNotificationService::formatMessage($sale, 'created');
        $job = new SendOrderNotificationJob($sale, 'created', $message);
        $job->handle();
    }

    public function test_can_view_sale_show_page(): void
    {
        [$customer, $product, $purchaseItem] = $this->createDependencies();

        $sale = Sale::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'SEP001',
            'sale_number' => 'SEP001',
            'sold_at' => now(),
            'subtotal' => 150,
            'tax_amount' => 27,
            'total_amount' => 177,
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'status' => 'Completed',
        ]);

        $response = $this->get(route('sales.show', $sale));

        $response->assertStatus(200);
        $response->assertSee('Sale Details');
        $response->assertSee('SEP001');
    }

    public function test_can_delete_sale_and_restore_stock(): void
    {
        [$customer, $product, $purchaseItem] = $this->createDependencies();

        $this->post(route('sales.store'), [
            'customer_id' => $customer->id,
            'sold_at' => '2026-09-10',
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'status' => 'Completed',
            'items' => [
                [
                    'product_id' => $product->id,
                    'barcode' => 'BC001',
                    'quantity' => 4,
                    'unit_price' => 150,
                    'tax_rate' => 18,
                ],
            ],
        ]);

        $sale = Sale::first();

        $response = $this->delete(route('sales.destroy', $sale));

        $response->assertRedirect(route('sales.index'));
        $this->assertDatabaseMissing('sales', ['id' => $sale->id]);

        $this->assertDatabaseHas('purchase_items', [
            'id' => $purchaseItem->id,
            'stock_quantity' => 10.000,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 10.000,
        ]);
    }
}
