<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Category',
    type: 'object',
    required: ['id', 'name'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', maxLength: 120, example: 'Camisetas'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Camisetas oversize y básicas.'),
        new OA\Property(property: 'products_count', type: 'integer', nullable: true, example: 12),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ],
)]
final class CategorySchema {}
