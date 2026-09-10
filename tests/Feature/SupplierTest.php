<?php

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_suppliers_index_page(): void
    {
        Supplier::create([
            'name' => 'Acme Supplies',
            'email' => 'acme@example.com',
            'phone' => '9888777666',
            'address' => 'Industrial Estate',
            'is_active' => true,
        ]);

        $response = $this->get(route('masters.suppliers.index'));

        $response->assertStatus(200);
        $response->assertSee('Acme Supplies');
        $response->assertSee('acme@example.com');
    }

    public function test_can_view_create_supplier_page(): void
    {
        $response = $this->get(route('masters.suppliers.new'));

        $response->assertRedirect(route('masters.suppliers.index'));
    }

    public function test_can_create_supplier(): void
    {
        $response = $this->post(route('masters.suppliers.store'), [
            'name' => 'Beta Traders',
            'email' => 'beta@example.com',
            'phone' => '9777666555',
            'address' => 'GIDC Area',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('masters.suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'name' => 'Beta Traders',
            'email' => 'beta@example.com',
            'is_active' => true,
        ]);
    }

    public function test_can_view_edit_supplier_page(): void
    {
        $supplier = Supplier::create([
            'name' => 'Gamma Logistics',
            'email' => 'gamma@example.com',
            'is_active' => true,
        ]);

        $response = $this->get(route('masters.suppliers.edit', $supplier));

        $response->assertRedirect(route('masters.suppliers.index'));
    }

    public function test_can_update_supplier(): void
    {
        $supplier = Supplier::create([
            'name' => 'Delta Imports',
            'email' => 'delta@example.com',
            'is_active' => true,
        ]);

        $response = $this->put(route('masters.suppliers.update', $supplier), [
            'name' => 'Delta Imports Updated',
            'email' => 'delta.updated@example.com',
            'phone' => '9666555444',
            'address' => 'Updated Warehouse',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('masters.suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Delta Imports Updated',
            'email' => 'delta.updated@example.com',
        ]);
    }

    public function test_can_delete_supplier_without_purchases(): void
    {
        $supplier = Supplier::create([
            'name' => 'Epsilon Traders',
            'email' => 'epsilon@example.com',
            'is_active' => true,
        ]);

        $response = $this->delete(route('masters.suppliers.destroy', $supplier));

        $response->assertRedirect(route('masters.suppliers.index'));
        $this->assertDatabaseMissing('suppliers', [
            'id' => $supplier->id,
        ]);
    }

    public function test_cannot_delete_supplier_associated_with_purchases(): void
    {
        $supplier = Supplier::create(['name' => 'Supplier With Purchase', 'is_active' => true]);

        Purchase::create([
            'supplier_id' => $supplier->id,
            'purchase_number' => 'PUR-001',
            'purchased_at' => now(),
            'subtotal' => 500,
            'tax_amount' => 90,
            'total_amount' => 590,
        ]);

        $response = $this->delete(route('masters.suppliers.destroy', $supplier));

        $response->assertRedirect(route('masters.suppliers.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
        ]);
    }
}
