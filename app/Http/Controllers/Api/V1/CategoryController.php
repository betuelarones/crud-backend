<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\IndexCategoryRequest;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Categorías')]
class CategoryController extends Controller
{
    #[OA\Get(
        path: '/api/v1/categories',
        tags: ['Categorías'],
        summary: 'Listar categorías activas',
        description: 'Devuelve las categorías no eliminadas con paginación y búsqueda por nombre.',
        parameters: [
            new OA\QueryParameter(name: 'search', description: 'Búsqueda parcial por nombre.', required: false, schema: new OA\Schema(type: 'string', maxLength: 120)),
            new OA\QueryParameter(name: 'page', description: 'Número de página.', required: false, schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\QueryParameter(name: 'per_page', description: 'Elementos por página (máx. 100).', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Listado paginado de categorías.', content: new OA\JsonContent(ref: '#/components/schemas/CategoryListSuccess')),
            new OA\Response(response: 422, description: 'Parámetros de consulta inválidos.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Error interno del servidor.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    public function index(IndexCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $categories = Category::query()
            ->withCount('products')
            ->when(
                $validated['search'] ?? null,
                fn ($query, string $search) => $query->where('name', 'like', "%{$search}%"),
            )
            ->orderBy('name')
            ->paginate((int) ($validated['per_page'] ?? 15))
            ->withQueryString();

        return ApiResponse::success([
            'items' => CategoryResource::collection($categories->items()),
            'pagination' => ApiResponse::pagination($categories),
        ], 'Categorías obtenidas correctamente.');
    }

    #[OA\Post(
        path: '/api/v1/categories',
        tags: ['Categorías'],
        summary: 'Crear categoría',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CategoryInput')),
        responses: [
            new OA\Response(response: 201, description: 'Categoría creada.', content: new OA\JsonContent(ref: '#/components/schemas/CategorySuccess')),
            new OA\Response(response: 422, description: 'Datos inválidos.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Error interno del servidor.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());
        $category->loadCount('products');

        return ApiResponse::success(
            new CategoryResource($category),
            'Categoría creada correctamente.',
            201,
        );
    }

    #[OA\Get(
        path: '/api/v1/categories/{id}',
        tags: ['Categorías'],
        summary: 'Consultar categoría',
        parameters: [
            new OA\PathParameter(name: 'id', description: 'ID de la categoría.', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Categoría encontrada.', content: new OA\JsonContent(ref: '#/components/schemas/CategorySuccess')),
            new OA\Response(response: 404, description: 'Categoría no encontrada.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Error interno del servidor.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    public function show(Category $category): JsonResponse
    {
        $category->loadCount('products');

        return ApiResponse::success(
            new CategoryResource($category),
            'Categoría obtenida correctamente.',
        );
    }

    #[OA\Put(
        path: '/api/v1/categories/{id}',
        tags: ['Categorías'],
        summary: 'Actualizar categoría',
        parameters: [
            new OA\PathParameter(name: 'id', description: 'ID de la categoría.', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CategoryInput')),
        responses: [
            new OA\Response(response: 200, description: 'Categoría actualizada.', content: new OA\JsonContent(ref: '#/components/schemas/CategorySuccess')),
            new OA\Response(response: 404, description: 'Categoría no encontrada.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Datos inválidos.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Error interno del servidor.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());
        $category->loadCount('products');

        return ApiResponse::success(
            new CategoryResource($category),
            'Categoría actualizada correctamente.',
        );
    }

    #[OA\Delete(
        path: '/api/v1/categories/{id}',
        tags: ['Categorías'],
        summary: 'Eliminar categoría (soft delete)',
        description: 'No se permite eliminar una categoría con productos activos.',
        parameters: [
            new OA\PathParameter(name: 'id', description: 'ID de la categoría.', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Categoría eliminada lógicamente. Sin cuerpo.'),
            new OA\Response(response: 404, description: 'Categoría no encontrada.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 409, description: 'La categoría tiene productos activos.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Error interno del servidor.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    public function destroy(Category $category): Response|JsonResponse
    {
        if ($category->products()->exists()) {
            return ApiResponse::error(
                'No se puede eliminar la categoría porque tiene productos activos.',
                [],
                409,
            );
        }

        $category->delete();

        return response()->noContent();
    }
}
