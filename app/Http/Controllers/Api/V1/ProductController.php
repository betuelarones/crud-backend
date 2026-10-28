<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\IndexProductRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Productos')]
class ProductController extends Controller
{
    #[OA\Get(
        path: '/api/v1/products',
        tags: ['Productos'],
        summary: 'Listar productos activos',
        description: 'Devuelve productos no eliminados con su categoría, búsqueda, filtro y paginación.',
        parameters: [
            new OA\QueryParameter(name: 'search', description: 'Búsqueda parcial por nombre.', required: false, schema: new OA\Schema(type: 'string', maxLength: 150)),
            new OA\QueryParameter(name: 'category_id', description: 'Filtra por categoría activa.', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'page', description: 'Número de página.', required: false, schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\QueryParameter(name: 'per_page', description: 'Elementos por página (máx. 100).', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Listado paginado de productos.', content: new OA\JsonContent(ref: '#/components/schemas/ProductListSuccess')),
            new OA\Response(response: 422, description: 'Parámetros de consulta inválidos.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Error interno del servidor.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    public function index(IndexProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $products = Product::query()
            ->with('category')
            ->when(
                $validated['search'] ?? null,
                fn ($query, string $search) => $query->where('name', 'like', "%{$search}%"),
            )
            ->when(
                $validated['category_id'] ?? null,
                fn ($query, int|string $categoryId) => $query->where('category_id', $categoryId),
            )
            ->latest('created_at')
            ->latest('id')
            ->paginate((int) ($validated['per_page'] ?? 15))
            ->withQueryString();

        return ApiResponse::success([
            'items' => ProductResource::collection($products->items()),
            'pagination' => ApiResponse::pagination($products),
        ], 'Productos obtenidos correctamente.');
    }

    #[OA\Post(
        path: '/api/v1/products',
        tags: ['Productos'],
        summary: 'Crear producto',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ProductInput')),
        responses: [
            new OA\Response(response: 201, description: 'Producto creado.', content: new OA\JsonContent(ref: '#/components/schemas/ProductSuccess')),
            new OA\Response(response: 422, description: 'Datos inválidos o categoría eliminada.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Error interno del servidor.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());
        $product->load('category');

        return ApiResponse::success(
            new ProductResource($product),
            'Producto creado correctamente.',
            201,
        );
    }

    #[OA\Get(
        path: '/api/v1/products/{id}',
        tags: ['Productos'],
        summary: 'Consultar producto',
        parameters: [
            new OA\PathParameter(name: 'id', description: 'ID del producto.', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Producto encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/ProductSuccess')),
            new OA\Response(response: 404, description: 'Producto no encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Error interno del servidor.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    public function show(Product $product): JsonResponse
    {
        $product->load('category');

        return ApiResponse::success(
            new ProductResource($product),
            'Producto obtenido correctamente.',
        );
    }

    #[OA\Put(
        path: '/api/v1/products/{id}',
        tags: ['Productos'],
        summary: 'Actualizar producto',
        parameters: [
            new OA\PathParameter(name: 'id', description: 'ID del producto.', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ProductInput')),
        responses: [
            new OA\Response(response: 200, description: 'Producto actualizado.', content: new OA\JsonContent(ref: '#/components/schemas/ProductSuccess')),
            new OA\Response(response: 404, description: 'Producto no encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Datos inválidos o categoría eliminada.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Error interno del servidor.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());
        $product->load('category');

        return ApiResponse::success(
            new ProductResource($product),
            'Producto actualizado correctamente.',
        );
    }

    #[OA\Delete(
        path: '/api/v1/products/{id}',
        tags: ['Productos'],
        summary: 'Eliminar producto (soft delete)',
        parameters: [
            new OA\PathParameter(name: 'id', description: 'ID del producto.', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Producto eliminado lógicamente. Sin cuerpo.'),
            new OA\Response(response: 404, description: 'Producto no encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Error interno del servidor.', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ],
    )]
    public function destroy(Product $product): Response
    {
        $product->delete();

        return response()->noContent();
    }
}
