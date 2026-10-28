<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorResponse',
    type: 'object',
    required: ['success', 'message'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Los datos proporcionados no son válidos.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            nullable: true,
            example: ['name' => ['El nombre es obligatorio.']],
        ),
    ],
)]
final class ErrorResponseSchema {}
