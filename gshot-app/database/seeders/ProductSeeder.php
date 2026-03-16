<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Cheese Burger',
            'description' => 'Delicious cheese burger',
            'price' => 10.99,
            'image' => 'images/Burger.jpg',
        ]);
    }
}
