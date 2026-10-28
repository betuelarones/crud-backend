<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_only_active_categories_with_pagination_metadata(): void
    {
        $active = Category::factory()->create(['name' => 'Activa']);
        $deleted = Category::factory()->create(['name' => 'Eliminada']);
        $deleted->delete();

        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Categorías obtenidas correctamente.')
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $active->id)
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'items',
                    'pagination' => ['current_page', 'per_page', 'total', 'last_page', 'from', 'to'],
                ],
            ]);
    }

    public function test_it_creates_a_category(): void
    {
        $response = $this->postJson('/api/v1/categories', [
            'name' => 'Camisetas',
            'description' => 'Camisetas de la colección.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Categoría creada correctamente.')
            ->assertJsonPath('data.name', 'Camisetas')
            ->assertJsonPath('data.products_count', 0);

        $this->assertDatabaseHas('categories', [
            'name' => 'Camisetas',
            'deleted_at' => null,
        ]);
    }

    public function test_it_validates_category_payload_and_duplicate_names(): void
    {
        Category::factory()->create(['name' => 'Camisetas']);

        $response = $this->postJson('/api/v1/categories', [
            'name' => 'Camisetas',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Los datos proporcionados no son válidos.')
            ->assertJsonValidationErrors(['name']);

        $this->postJson('/api/v1/categories', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_it_allows_reusing_the_name_of_a_soft_deleted_category(): void
    {
        $category = Category::factory()->create(['name' => 'Camisetas']);
        $category->delete();

        $this->postJson('/api/v1/categories', ['name' => 'Camisetas'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Camisetas');
    }

    public function test_it_shows_updates_and_returns_404_for_deleted_categories(): void
    {
        $category = Category::factory()->create(['name' => 'Original']);

        $this->getJson("/api/v1/categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Original');

        $this->putJson("/api/v1/categories/{$category->id}", [
            'name' => 'Actualizada',
            'description' => 'Nueva descripción.',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Actualizada');

        $category->delete();

        $this->getJson("/api/v1/categories/{$category->id}")
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Recurso no encontrado.');
    }

    public function test_it_blocks_deleting_a_category_with_active_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $this->deleteJson("/api/v1/categories/{$category->id}")
            ->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'No se puede eliminar la categoría porque tiene productos activos.');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'deleted_at' => null,
        ]);
    }

    public function test_it_soft_deletes_a_category_when_it_has_no_active_products(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $product->delete();

        $this->deleteJson("/api/v1/categories/{$category->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_it_validates_index_query_parameters(): void
    {
        $this->getJson('/api/v1/categories?per_page=0')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }
}
