<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'The ultimate iPhone.',
                'price' => 999.00,
                'image' => 'https://example.com/iphone.jpg',
            ],
            [
                'name' => 'MacBook Air M3',
                'description' => 'Power. It’s in the Air.',
                'price' => 1099.00,
                'image' => 'https://example.com/macbook.jpg',
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'description' => 'Industry-leading noise cancellation.',
                'price' => 348.00,
                'image' => 'https://example.com/sony.jpg',
            ],
            [
                'name' => 'Logitech MX Master 3S',
                'description' => 'Performance Wireless Mouse.',
                'price' => 99.99,
                'image' => 'https://example.com/logitech.jpg',
            ],
        ];

        foreach ($products as $product) {
            \App\Models\Product::create($product);
        }
    }
}
