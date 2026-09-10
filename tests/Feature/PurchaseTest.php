<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    private function createDependencies(): array
    {
        $supplier = Supplier::create([
            'name' => 'Supplier A',
            'email' => 'suppa@example.com',
            'is_active' => true,
        ]);

        $category = Category::create(['name' => 'General']);
        $unit = Unit::create(['name' => 'Pieces', 'short_name' => 'pcs', 'is_active' => true]);
        $tax = Tax::create(['name' => 'GST 18%', 'rate' => 18, 'is_active' => true]);

        $product = Product::create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'tax_id' => $tax->id,
            'name' => 'Widget A',
            'sku' => 'WID-01',
            'purchase_price' => 100,
            'selling_price' => 150,
            'stock_quantity' => 0,
            'is_active' => true,
        ]);

        return [$supplier, $product];
    }

    public function test_can_view_purchases_index_page(): void
    {
        [$supplier, $product] = $this->createDependencies();

        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'purchase_number' => 'SEP001',
            'purchased_at' => now(),
            'subtotal' => 1000,
            'tax_amount' => 180,
            'total_amount' => 1180,
        ]);

        $response = $this->get(route('purchases.index'));

        $response->assertStatus(200);
        $response->assertSee('SEP001');
        $response->assertSee('Supplier A');
    }

    public function test_can_view_create_purchase_page_via_new_and_create_routes(): void
    {
        $responseNew = $this->get('/purchases/new');
        $responseNew->assertStatus(200);
        $responseNew->assertViewIs('purchases.create');
        $responseNew->assertSee('Create Purchase Order');

        $responseCreate = $this->get('/purchases/create');
        $responseCreate->assertStatus(200);
        $responseCreate->assertViewIs('purchases.create');
        $responseCreate->assertSee('Create Purchase Order');
    }

    public function test_returns_404_for_non_existent_purchase(): void
    {
        $response = $this->get('/purchases/999999');
        $response->assertStatus(404);
    }

    public function test_can_create_purchase_with_auto_generated_number_and_stock_logs(): void
    {
        [$supplier, $product] = $this->createDependencies();

        $response = $this->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'purchased_at' => '2026-09-10',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_cost' => 100,
                    'tax_rate' => 18,
                ],
            ],
        ]);

        $response->assertRedirect(route('purchases.index'));
        $this->assertDatabaseHas('purchases', [
            'supplier_id' => $supplier->id,
            'subtotal' => 1000,
            'tax_amount' => 180,
            'total_amount' => 1180,
        ]);

        $this->assertDatabaseHas('purchase_items', [
            'product_id' => $product->id,
            'quantity' => 10,
            'stock_quantity' => 10,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 10.000,
        ]);

        $this->assertDatabaseHas('stock_logs', [
            'product_id' => $product->id,
            'area' => 'purchase',
            'mode' => '+',
            'quantity' => 10,
        ]);
    }

    public function test_can_view_purchase_show_page(): void
    {
        [$supplier, $product] = $this->createDependencies();

        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'purchase_number' => 'SEP001',
            'purchased_at' => now(),
            'subtotal' => 1000,
            'tax_amount' => 180,
            'total_amount' => 1180,
        ]);

        $response = $this->get(route('purchases.show', $purchase));

        $response->assertStatus(200);
        $response->assertSee('Purchase Details');
        $response->assertSee('SEP001');
    }

    public function test_can_view_edit_purchase_page(): void
    {
        [$supplier, $product] = $this->createDependencies();

        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'purchase_number' => 'SEP001',
            'purchased_at' => now(),
            'subtotal' => 1000,
            'tax_amount' => 180,
            'total_amount' => 1180,
        ]);

        $response = $this->get(route('purchases.edit', $purchase));

        $response->assertStatus(200);
        $response->assertSee('Edit Purchase');
    }

    public function test_can_delete_purchase_and_adjust_stock(): void
    {
        [$supplier, $product] = $this->createDependencies();

        $this->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'purchased_at' => '2026-09-10',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_cost' => 100,
                    'tax_rate' => 18,
                ],
            ],
        ]);

        $purchase = Purchase::first();

        $response = $this->delete(route('purchases.destroy', $purchase));

        $response->assertRedirect(route('purchases.index'));
        $this->assertDatabaseMissing('purchases', ['id' => $purchase->id]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 0.000,
        ]);
    }
}
