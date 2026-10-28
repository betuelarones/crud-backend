<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProductInput',
    type: 'object',
    required: ['name', 'price', 'stock', 'category_id'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 150, example: 'Camiseta Overskull Black'),
        new OA\Property(property: 'description', type: 'string', nullable: true, maxLength: 2000, example: 'Camiseta de algodón con estampado frontal.'),
        new OA\Property(property: 'price', type: 'number', format: 'float', minimum: 0.01, example: 89.90),
        new OA\Property(property: 'stock', type: 'integer', minimum: 0, example: 50),
        new OA\Property(property: 'category_id', type: 'integer', example: 1),
    ],
)]
final class ProductInputSchema {}
