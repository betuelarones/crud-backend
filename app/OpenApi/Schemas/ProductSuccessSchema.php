<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProductSuccess',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Producto creado correctamente.'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Product'),
    ],
)]
final class ProductSuccessSchema {}
