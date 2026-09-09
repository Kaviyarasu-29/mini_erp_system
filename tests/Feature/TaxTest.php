<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tax;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_taxes_index_page(): void
    {
        Tax::create(['name' => 'GST 18%', 'rate' => 18.00, 'is_active' => true]);

        $response = $this->get(route('masters.taxes.index'));

        $response->assertStatus(200);
        $response->assertSee('GST 18%');
        $response->assertSee('18');
    }

    public function test_can_create_tax(): void
    {
        $response = $this->post(route('masters.taxes.store'), [
            'name' => 'GST 5%',
            'rate' => 5.00,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('masters.taxes.index'));
        $this->assertDatabaseHas('taxes', [
            'name' => 'GST 5%',
            'rate' => 5.00,
            'is_active' => true,
        ]);
    }

    public function test_can_update_tax(): void
    {
        $tax = Tax::create(['name' => 'GST 12%', 'rate' => 12.00, 'is_active' => true]);

        $response = $this->put(route('masters.taxes.update', $tax), [
            'name' => 'GST 12% Revised',
            'rate' => 12.50,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('masters.taxes.index'));
        $this->assertDatabaseHas('taxes', [
            'id' => $tax->id,
            'name' => 'GST 12% Revised',
            'rate' => 12.50,
        ]);
    }

    public function test_can_delete_tax_without_products(): void
    {
        $tax = Tax::create(['name' => 'GST 28%', 'rate' => 28.00, 'is_active' => true]);

        $response = $this->delete(route('masters.taxes.destroy', $tax));

        $response->assertRedirect(route('masters.taxes.index'));
        $this->assertDatabaseMissing('taxes', [
            'id' => $tax->id,
        ]);
    }

    public function test_cannot_delete_tax_associated_with_products(): void
    {
        $tax = Tax::create(['name' => 'GST 18%', 'rate' => 18.00, 'is_active' => true]);
        $category = Category::create(['name' => 'General']);
        $unit = Unit::create(['name' => 'Pieces', 'short_name' => 'pcs', 'is_active' => true]);

        Product::create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'tax_id' => $tax->id,
            'name' => 'Taxed Product',
            'sku' => 'PROD-TAX-001',
            'purchase_price' => 100,
            'selling_price' => 150,
            'stock_quantity' => 0,
            'is_active' => true,
        ]);

        $response = $this->delete(route('masters.taxes.destroy', $tax));

        $response->assertRedirect(route('masters.taxes.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('taxes', [
            'id' => $tax->id,
        ]);
    }
}
