<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_categories_index_page(): void
    {
        $category = Category::create(['name' => 'Electronics']);

        $response = $this->get(route('masters.categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Electronics');
    }

    public function test_can_create_parent_category(): void
    {
        $response = $this->post(route('masters.categories.store'), [
            'name' => 'Hardware',
            'parent_id' => null,
        ]);

        $response->assertRedirect(route('masters.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Hardware',
            'parent_id' => null,
        ]);
    }

    public function test_can_create_subcategory(): void
    {
        $parent = Category::create(['name' => 'Electronics']);

        $response = $this->post(route('masters.categories.store'), [
            'name' => 'Mobile Phones',
            'parent_id' => $parent->id,
        ]);

        $response->assertRedirect(route('masters.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Mobile Phones',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_can_update_category(): void
    {
        $category = Category::create(['name' => 'Old Category']);

        $response = $this->put(route('masters.categories.update', $category), [
            'name' => 'Updated Category',
            'parent_id' => null,
        ]);

        $response->assertRedirect(route('masters.categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
        ]);
    }

    public function test_can_delete_category_without_children(): void
    {
        $category = Category::create(['name' => 'Temporary Category']);

        $response = $this->delete(route('masters.categories.destroy', $category));

        $response->assertRedirect(route('masters.categories.index'));
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_cannot_delete_category_with_subcategories(): void
    {
        $parent = Category::create(['name' => 'Electronics']);
        Category::create(['name' => 'Mobile Phones', 'parent_id' => $parent->id]);

        $response = $this->delete(route('masters.categories.destroy', $parent));

        $response->assertRedirect(route('masters.categories.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', [
            'id' => $parent->id,
        ]);
    }
}
