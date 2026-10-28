<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['category' => 'Camisetas', 'name' => 'Camiseta Overskull Black', 'price' => 89.90, 'stock' => 50, 'description' => 'Camiseta de algodón con estampado frontal.'],
            ['category' => 'Camisetas', 'name' => 'Camiseta Overskull Bone', 'price' => 89.90, 'stock' => 35, 'description' => 'Camiseta oversize en tono hueso.'],
            ['category' => 'Camisetas', 'name' => 'Camiseta Logo Mono', 'price' => 79.90, 'stock' => 20, 'description' => 'Camiseta minimalista con logo bordado.'],
            ['category' => 'Hoodies', 'name' => 'Hoodie Overskull Ink', 'price' => 199.90, 'stock' => 18, 'description' => 'Sudadera heavyweight con capucha forrada.'],
            ['category' => 'Hoodies', 'name' => 'Hoodie Skull Patch', 'price' => 219.90, 'stock' => 12, 'description' => 'Hoodie con parche bordado en el pecho.'],
            ['category' => 'Hoodies', 'name' => 'Hoodie Essential Grey', 'price' => 179.90, 'stock' => 25, 'description' => 'Sudadera esencial en gris melange.'],
            ['category' => 'Accesorios', 'name' => 'Gorra Overskull Snapback', 'price' => 69.90, 'stock' => 40, 'description' => 'Gorra ajustable con logo metálico.'],
            ['category' => 'Accesorios', 'name' => 'Tote Bag Canvas', 'price' => 49.90, 'stock' => 60, 'description' => 'Bolso de lona resistente para el día a día.'],
            ['category' => 'Accesorios', 'name' => 'Calcetines Logo Pack', 'price' => 29.90, 'stock' => 80, 'description' => 'Pack de tres pares de calcetines.'],
            ['category' => 'Calzado', 'name' => 'Zapatilla Court White', 'price' => 259.90, 'stock' => 15, 'description' => 'Zapatilla urbana de caña baja.'],
            ['category' => 'Calzado', 'name' => 'Zapatilla Runner Black', 'price' => 289.90, 'stock' => 10, 'description' => 'Zapatilla deportiva con suela acolchada.'],
            ['category' => 'Calzado', 'name' => 'Chancla Summer Sand', 'price' => 99.90, 'stock' => 30, 'description' => 'Chancla cómoda para temporada de verano.'],
        ];

        foreach ($products as $item) {
            $category = Category::where('name', $item['category'])->first();

            if (! $category) {
                continue;
            }

            Product::firstOrCreate(
                ['name' => $item['name']],
                [
                    'category_id' => $category->id,
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'stock' => $item['stock'],
                ],
            );
        }
    }
}
