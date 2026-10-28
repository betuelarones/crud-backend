<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CategoryListSuccess',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Categorías obtenidas correctamente.'),
        new OA\Property(
            property: 'data',
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Category'),
                ),
                new OA\Property(property: 'pagination', ref: '#/components/schemas/PaginationMeta'),
            ],
        ),
    ],
)]
final class CategoryListSuccessSchema {}
