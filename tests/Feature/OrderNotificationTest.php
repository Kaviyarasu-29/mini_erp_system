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
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function createDependencies(): array
    {
        $supplier = Supplier::create(['name' => 'Supplier A', 'is_active' => true]);
        $customer = Customer::create(['name' => 'John Doe', 'email' => 'john@example.com', 'is_active' => true]);
        $category = Category::create(['name' => 'Electronics']);
        $unit = Unit::create(['name' => 'Pieces', 'short_name' => 'pcs', 'is_active' => true]);
        $tax = Tax::create(['name' => 'GST 18%', 'rate' => 18, 'is_active' => true]);

        $product = Product::create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'tax_id' => $tax->id,
            'name' => 'Laptop',
            'sku' => 'LAP-01',
            'purchase_price' => 50000,
            'selling_price' => 65000,
            'stock_quantity' => 20,
            'is_active' => true,
        ]);

        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'purchase_number' => 'SEP001',
            'purchased_at' => now(),
            'subtotal' => 500000,
            'tax_amount' => 90000,
            'total_amount' => 590000,
        ]);

        $purchaseItem = PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'barcode' => 'BC100',
            'quantity' => 10,
            'stock_quantity' => 10,
            'unit_cost' => 50000,
            'tax_rate' => 18,
            'tax_amount' => 9000,
            'line_total' => 59000,
        ]);

        return [$customer, $product, $purchaseItem];
    }

    public function test_service_formats_all_four_notification_templates(): void
    {
        [$customer, $product, $purchaseItem] = $this->createDependencies();

        $sale = Sale::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-2026-001',
            'sale_number' => 'INV-2026-001',
            'sold_at' => now(),
            'subtotal' => 65000,
            'tax_amount' => 11700,
            'total_amount' => 76700,
            'payment_method' => 'Bank Transfer',
            'payment_status' => 'Paid',
            'status' => 'Completed',
        ]);

        $createdMsg = OrderNotificationService::formatMessage($sale, 'created');
        $updatedMsg = OrderNotificationService::formatMessage($sale, 'updated');
        $completedMsg = OrderNotificationService::formatMessage($sale, 'completed');
        $cancelledMsg = OrderNotificationService::formatMessage($sale, 'cancelled');

        $this->assertStringContainsString('[ORDER CREATED] Invoice #INV-2026-001 created for John Doe <john@example.com>', $createdMsg);
        $this->assertStringContainsString('[ORDER UPDATED] Invoice #INV-2026-001 updated for John Doe <john@example.com>', $updatedMsg);
        $this->assertStringContainsString('[ORDER COMPLETED] Invoice #INV-2026-001 for John Doe <john@example.com> marked as Completed', $completedMsg);
        $this->assertStringContainsString('[ORDER CANCELLED] Invoice #INV-2026-001 for John Doe <john@example.com> has been Cancelled', $cancelledMsg);
    }

    public function test_updating_sale_dispatches_updated_notification_job(): void
    {
        Queue::fake();

        [$customer, $product, $purchaseItem] = $this->createDependencies();

        $sale = Sale::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-2026-002',
            'sale_number' => 'INV-2026-002',
            'sold_at' => now(),
            'subtotal' => 65000,
            'tax_amount' => 11700,
            'total_amount' => 76700,
            'payment_method' => 'Bank Transfer',
            'payment_status' => 'Pending',
            'status' => 'Pending',
        ]);

        $response = $this->put(route('sales.update', $sale), [
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-2026-002',
            'sold_at' => '2026-09-10',
            'payment_method' => 'Bank Transfer',
            'payment_status' => 'Paid',
            'status' => 'Completed',
            'items' => [
                [
                    'product_id' => $product->id,
                    'barcode' => 'BC100',
                    'quantity' => 1,
                    'unit_price' => 65000,
                    'tax_rate' => 18,
                ],
            ],
        ]);

        $response->assertRedirect(route('sales.index'));

        Queue::assertPushed(SendOrderNotificationJob::class, function (SendOrderNotificationJob $job) {
            return $job->event === 'updated';
        });

        Queue::assertPushed(SendOrderNotificationJob::class, function (SendOrderNotificationJob $job) {
            return $job->event === 'completed';
        });
    }

    public function test_deleting_sale_dispatches_cancelled_notification_job(): void
    {
        Queue::fake();

        [$customer, $product, $purchaseItem] = $this->createDependencies();

        $sale = Sale::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-2026-003',
            'sale_number' => 'INV-2026-003',
            'sold_at' => now(),
            'subtotal' => 65000,
            'tax_amount' => 11700,
            'total_amount' => 76700,
            'payment_method' => 'Bank Transfer',
            'payment_status' => 'Pending',
            'status' => 'Pending',
        ]);

        $response = $this->delete(route('sales.destroy', $sale));

        $response->assertRedirect(route('sales.index'));

        Queue::assertPushed(SendOrderNotificationJob::class, function (SendOrderNotificationJob $job) {
            return $job->event === 'cancelled';
        });
    }
}
