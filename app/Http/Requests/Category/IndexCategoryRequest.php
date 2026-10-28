<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class IndexCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'search.string' => 'La búsqueda debe ser texto.',
            'search.max' => 'La búsqueda no puede superar los 120 caracteres.',
            'page.integer' => 'La página debe ser un número entero.',
            'page.min' => 'La página debe ser mayor o igual a 1.',
            'per_page.integer' => 'Los elementos por página deben ser un número entero.',
            'per_page.min' => 'Los elementos por página deben ser al menos 1.',
            'per_page.max' => 'Los elementos por página no pueden superar 100.',
        ];
    }
}
