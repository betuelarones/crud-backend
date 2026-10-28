<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CategoryInput',
    type: 'object',
    required: ['name'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 120, example: 'Camisetas'),
        new OA\Property(property: 'description', type: 'string', nullable: true, maxLength: 2000, example: 'Camisetas oversize y básicas.'),
    ],
)]
final class CategoryInputSchema {}
