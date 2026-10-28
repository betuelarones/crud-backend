<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CategorySuccess',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Categoría obtenida correctamente.'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Category'),
    ],
)]
final class CategorySuccessSchema {}
