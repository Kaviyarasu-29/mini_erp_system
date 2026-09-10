<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_products_index_page(): void
    {
        $category = Category::create(['name' => 'Electronics']);
        $unit = Unit::create(['name' => 'Pieces', 'short_name' => 'pcs', 'is_active' => true]);

        Product::create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => 'Smartphone X',
            'sku' => 'ELEC-SMART-01',
            'purchase_price' => 10000,
            'selling_price' => 15000,
            'stock_quantity' => 0,
            'is_active' => true,
        ]);

        $response = $this->get(route('masters.products.index'));

        $response->assertStatus(200);
        $response->assertSee('Smartphone X');
        $response->assertSee('ELEC-SMART-01');
    }

    public function test_can_view_create_product_page(): void
    {
        $response = $this->get(route('masters.products.new'));

        $response->assertRedirect(route('masters.products.index'));
    }

    public function test_can_create_product(): void
    {
        $category = Category::create(['name' => 'Electronics']);
        $unit = Unit::create(['name' => 'Pieces', 'short_name' => 'pcs', 'is_active' => true]);
        $tax = Tax::create(['name' => 'GST 18%', 'rate' => 18, 'is_active' => true]);

        $response = $this->post(route('masters.products.store'), [
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'tax_id' => $tax->id,
            'name' => 'Wireless Keyboard',
            'sku' => 'ELEC-KEY-01',
            'purchase_price' => 1200,
            'selling_price' => 1999,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('masters.products.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'Wireless Keyboard',
            'sku' => 'ELEC-KEY-01',
            'stock_quantity' => 0.000,
            'is_active' => true,
        ]);
    }

    public function test_can_view_edit_product_page(): void
    {
        $category = Category::create(['name' => 'Electronics']);
        $unit = Unit::create(['name' => 'Pieces', 'short_name' => 'pcs', 'is_active' => true]);

        $product = Product::create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => 'Gaming Mouse',
            'sku' => 'ELEC-MOU-01',
            'purchase_price' => 800,
            'selling_price' => 1200,
            'stock_quantity' => 0,
            'is_active' => true,
        ]);

        $response = $this->get(route('masters.products.edit', $product));

        $response->assertRedirect(route('masters.products.index'));
    }

    public function test_can_update_product(): void
    {
        $category = Category::create(['name' => 'Electronics']);
        $unit = Unit::create(['name' => 'Pieces', 'short_name' => 'pcs', 'is_active' => true]);

        $product = Product::create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => 'Old Product Name',
            'sku' => 'ELEC-OLD-01',
            'purchase_price' => 500,
            'selling_price' => 800,
            'stock_quantity' => 0,
            'is_active' => true,
        ]);

        $response = $this->put(route('masters.products.update', $product), [
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => 'Updated Product Name',
            'sku' => 'ELEC-OLD-01',
            'purchase_price' => 550,
            'selling_price' => 900,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('masters.products.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'selling_price' => 900,
        ]);
    }

    public function test_can_delete_product_without_transactions(): void
    {
        $category = Category::create(['name' => 'Electronics']);
        $unit = Unit::create(['name' => 'Pieces', 'short_name' => 'pcs', 'is_active' => true]);

        $product = Product::create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => 'Unused Product',
            'sku' => 'ELEC-UNUSED-01',
            'purchase_price' => 100,
            'selling_price' => 150,
            'stock_quantity' => 0,
            'is_active' => true,
        ]);

        $response = $this->delete(route('masters.products.destroy', $product));

        $response->assertRedirect(route('masters.products.index'));
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_cannot_delete_product_associated_with_purchase_items(): void
    {
        $supplier = Supplier::create(['name' => 'Supplier X', 'is_active' => true]);
        $category = Category::create(['name' => 'Electronics']);
        $unit = Unit::create(['name' => 'Pieces', 'short_name' => 'pcs', 'is_active' => true]);

        $product = Product::create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => 'Purchased Product',
            'sku' => 'ELEC-PUR-01',
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

        PurchaseItem::create([
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

        $response = $this->delete(route('masters.products.destroy', $product));

        $response->assertRedirect(route('masters.products.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
        ]);
    }
}
