<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_customers_index_page(): void
    {
        Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '9876543210',
            'address' => '123 Main St',
            'is_active' => true,
        ]);

        $response = $this->get(route('masters.customers.index'));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('john@example.com');
    }

    public function test_can_view_create_customer_page(): void
    {
        $response = $this->get(route('masters.customers.new'));

        $response->assertRedirect(route('masters.customers.index'));
    }

    public function test_can_create_customer(): void
    {
        $response = $this->post(route('masters.customers.store'), [
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
            'phone' => '9812345678',
            'address' => '45 Park Ave',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('masters.customers.index'));
        $this->assertDatabaseHas('customers', [
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
            'phone' => '9812345678',
            'is_active' => true,
        ]);
    }

    public function test_can_view_edit_customer_page(): void
    {
        $customer = Customer::create([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'phone' => '9988776655',
            'is_active' => true,
        ]);

        $response = $this->get(route('masters.customers.edit', $customer));

        $response->assertRedirect(route('masters.customers.index'));
    }

    public function test_can_update_customer(): void
    {
        $customer = Customer::create([
            'name' => 'Charlie Brown',
            'email' => 'charlie@example.com',
            'phone' => '9777666555',
            'is_active' => true,
        ]);

        $response = $this->put(route('masters.customers.update', $customer), [
            'name' => 'Charlie Brown Updated',
            'email' => 'charlie.updated@example.com',
            'phone' => '9777666555',
            'address' => '78 Oak St',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('masters.customers.index'));
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Charlie Brown Updated',
            'email' => 'charlie.updated@example.com',
        ]);
    }

    public function test_can_delete_customer_without_sales(): void
    {
        $customer = Customer::create([
            'name' => 'David Lee',
            'email' => 'david@example.com',
            'is_active' => true,
        ]);

        $response = $this->delete(route('masters.customers.destroy', $customer));

        $response->assertRedirect(route('masters.customers.index'));
        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_cannot_delete_customer_associated_with_sales(): void
    {
        $customer = Customer::create(['name' => 'Customer With Sale', 'is_active' => true]);

        Sale::create([
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-001',
            'sale_number' => 'INV-001',
            'sold_at' => now(),
            'subtotal' => 100,
            'tax_amount' => 18,
            'total_amount' => 118,
            'payment_method' => 'Cash',
            'payment_status' => 'Paid',
            'status' => 'Completed',
        ]);

        $response = $this->delete(route('masters.customers.destroy', $customer));

        $response->assertRedirect(route('masters.customers.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
        ]);
    }
}
