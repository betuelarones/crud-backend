<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Product',
    type: 'object',
    required: ['id', 'name', 'price', 'stock', 'category_id'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', maxLength: 150, example: 'Camiseta Overskull Black'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Camiseta de algodón con estampado frontal.'),
        new OA\Property(property: 'price', type: 'string', example: '89.90'),
        new OA\Property(property: 'stock', type: 'integer', example: 50),
        new OA\Property(property: 'category_id', type: 'integer', example: 1),
        new OA\Property(property: 'category', ref: '#/components/schemas/Category', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ],
)]
final class ProductSchema {}
