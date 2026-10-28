<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Camisetas', 'description' => 'Camisetas oversize y básicas de la colección.'],
            ['name' => 'Hoodies', 'description' => 'Sudadera con capucha para todos los días.'],
            ['name' => 'Accesorios', 'description' => 'Gorras, bolsos y detalles que completan el look.'],
            ['name' => 'Calzado', 'description' => 'Zapatillas y calzado urbano.'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']],
            );
        }
    }
}
