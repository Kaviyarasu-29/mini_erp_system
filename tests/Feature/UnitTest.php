<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_units_index_page(): void
    {
        Unit::create(['name' => 'Pieces', 'short_name' => 'pcs', 'is_active' => true]);

        $response = $this->get(route('masters.units.index'));

        $response->assertStatus(200);
        $response->assertSee('Pieces');
        $response->assertSee('pcs');
    }

    public function test_can_create_unit(): void
    {
        $response = $this->post(route('masters.units.store'), [
            'name' => 'Kilograms',
            'short_name' => 'kg',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('masters.units.index'));
        $this->assertDatabaseHas('units', [
            'name' => 'Kilograms',
            'short_name' => 'kg',
            'is_active' => true,
        ]);
    }

    public function test_can_update_unit(): void
    {
        $unit = Unit::create(['name' => 'Grams', 'short_name' => 'gm', 'is_active' => true]);

        $response = $this->put(route('masters.units.update', $unit), [
            'name' => 'Grams Updated',
            'short_name' => 'g',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('masters.units.index'));
        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'name' => 'Grams Updated',
            'short_name' => 'g',
        ]);
    }

    public function test_can_delete_unit_without_products(): void
    {
        $unit = Unit::create(['name' => 'Liters', 'short_name' => 'L', 'is_active' => true]);

        $response = $this->delete(route('masters.units.destroy', $unit));

        $response->assertRedirect(route('masters.units.index'));
        $this->assertDatabaseMissing('units', [
            'id' => $unit->id,
        ]);
    }

    public function test_cannot_delete_unit_associated_with_products(): void
    {
        $unit = Unit::create(['name' => 'Pieces', 'short_name' => 'pcs', 'is_active' => true]);
        $category = Category::create(['name' => 'General']);

        Product::create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => 'Sample Product',
            'sku' => 'PROD-001',
            'purchase_price' => 100,
            'selling_price' => 150,
            'stock_quantity' => 0,
            'is_active' => true,
        ]);

        $response = $this->delete(route('masters.units.destroy', $unit));

        $response->assertRedirect(route('masters.units.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
        ]);
    }
}
