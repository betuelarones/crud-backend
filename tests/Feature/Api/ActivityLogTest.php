<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_category_creation_update_and_deletion(): void
    {
        $response = $this->postJson('/api/v1/categories', [
            'name' => 'Camisetas',
            'description' => 'Colección principal.',
        ])->assertCreated();

        $categoryId = $response->json('data.id');

        $created = Activity::query()
            ->where('log_name', 'category')
            ->where('event', 'created')
            ->where('subject_id', $categoryId)
            ->first();

        $this->assertNotNull($created);
        $this->assertSame(Category::class, $created->subject_type);
        $this->assertNull($created->causer_id);
        $this->assertSame('Camisetas', $created->attribute_changes->get('attributes')['name']);

        $this->putJson("/api/v1/categories/{$categoryId}", [
            'name' => 'Camisetas Premium',
        ])->assertOk();

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'category',
            'event' => 'updated',
            'subject_id' => $categoryId,
        ]);

        $this->deleteJson("/api/v1/categories/{$categoryId}")->assertNoContent();

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'category',
            'event' => 'deleted',
            'subject_id' => $categoryId,
        ]);
    }

    public function test_it_logs_product_changes_without_duplicate_events(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/v1/products', [
            'name' => 'Hoodie Negro',
            'price' => 199.90,
            'stock' => 10,
            'category_id' => $category->id,
        ])->assertCreated();

        $productId = $response->json('data.id');

        $this->assertSame(
            1,
            Activity::query()
                ->where('log_name', 'product')
                ->where('event', 'created')
                ->where('subject_id', $productId)
                ->count(),
        );

        $this->putJson("/api/v1/products/{$productId}", [
            'name' => 'Hoodie Negro',
            'price' => 199.90,
            'stock' => 10,
            'category_id' => $category->id,
        ])->assertOk();

        $this->assertSame(
            0,
            Activity::query()
                ->where('log_name', 'product')
                ->where('event', 'updated')
                ->where('subject_id', $productId)
                ->count(),
            'No se debe registrar una actividad cuando no hay cambios reales.',
        );

        $this->deleteJson("/api/v1/products/{$productId}")->assertNoContent();

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'product',
            'event' => 'deleted',
            'subject_id' => $productId,
            'subject_type' => Product::class,
        ]);
    }
}
