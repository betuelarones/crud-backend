<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Overskul Products & Categories API',
    description: 'API REST para la gestión de productos y categorías con eliminación lógica, validación, auditoría y documentación OpenAPI.',
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'Servidor local de la API',
)]
#[OA\Tag(name: 'Categorías', description: 'Operaciones sobre categorías.')]
#[OA\Tag(name: 'Productos', description: 'Operaciones sobre productos.')]
final class OpenApiSpec {}
