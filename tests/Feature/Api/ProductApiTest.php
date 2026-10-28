<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_products_with_category_information_and_pagination(): void
    {
        $category = Category::factory()->create(['name' => 'Hoodies']);
        Product::factory()->count(3)->create(['category_id' => $category->id]);
        $deleted = Product::factory()->create(['category_id' => $category->id]);
        $deleted->delete();

        $response = $this->getJson('/api/v1/products?per_page=2');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.pagination.total', 3)
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonCount(2, 'data.items')
            ->assertJsonPath('data.items.0.category.name', 'Hoodies')
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => ['id', 'name', 'price', 'stock', 'category_id', 'category'],
                    ],
                    'pagination',
                ],
            ]);
    }

    public function test_it_filters_products_by_search_and_category(): void
    {
        $hoodies = Category::factory()->create();
        $shoes = Category::factory()->create();

        Product::factory()->create(['name' => 'Hoodie Negro', 'category_id' => $hoodies->id]);
        Product::factory()->create(['name' => 'Hoodie Gris', 'category_id' => $hoodies->id]);
        Product::factory()->create(['name' => 'Zapatilla Blanca', 'category_id' => $shoes->id]);

        $this->getJson('/api/v1/products?search=Hoodie')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 2);

        $this->getJson("/api/v1/products?category_id={$shoes->id}")
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.name', 'Zapatilla Blanca');
    }

    public function test_it_creates_a_product_with_two_decimal_price(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Camiseta Overskull',
            'description' => 'Camiseta negra.',
            'price' => 89.9,
            'stock' => 50,
            'category_id' => $category->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Producto creado correctamente.')
            ->assertJsonPath('data.price', '89.90')
            ->assertJsonPath('data.stock', 50)
            ->assertJsonPath('data.category.id', $category->id);

        $this->assertDatabaseHas('products', [
            'name' => 'Camiseta Overskull',
            'price' => 89.90,
            'stock' => 50,
            'category_id' => $category->id,
        ]);
    }

    public function test_it_validates_product_fields_and_active_category(): void
    {
        $deletedCategory = Category::factory()->create();
        $deletedCategory->delete();

        $response = $this->postJson('/api/v1/products', [
            'name' => '',
            'price' => 0,
            'stock' => -2,
            'category_id' => $deletedCategory->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Los datos proporcionados no son válidos.')
            ->assertJsonValidationErrors(['name', 'price', 'stock', 'category_id']);
    }

    public function test_it_updates_a_product_but_rejects_a_deleted_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Producto actualizado',
            'price' => 120.5,
            'stock' => 8,
            'category_id' => $category->id,
        ])->assertOk()
            ->assertJsonPath('data.name', 'Producto actualizado')
            ->assertJsonPath('data.price', '120.50');

        $otherCategory = Category::factory()->create();
        $otherCategory->delete();

        $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Producto actualizado',
            'price' => 120.5,
            'stock' => 8,
            'category_id' => $otherCategory->id,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id']);
    }

    public function test_it_soft_deletes_a_product_and_hides_it_from_normal_queries(): void
    {
        $product = Product::factory()->create();

        $this->deleteJson("/api/v1/products/{$product->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('products', ['id' => $product->id]);

        $this->getJson('/api/v1/products')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 0);

        $this->getJson("/api/v1/products/{$product->id}")
            ->assertNotFound();
    }

    public function test_it_validates_index_query_parameters(): void
    {
        $this->getJson('/api/v1/products?per_page=101')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);

        $this->getJson('/api/v1/products?category_id=999999')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id']);
    }
}
