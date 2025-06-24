<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample products
        $products = [
            [
                'part_number' => 'BRK-001',
                'name' => 'Brake Pad Set',
                'category_id' => 1,
                'brand_id' => 1,
                'location' => 'A1-B2',
                'description' => 'Front brake pad set for sedan vehicles',
                'quantity' => 0,
                'purchase_price' => 45.00,
                'selling_price' => 85.00
            ],
            [
                'part_number' => 'ENG-002',
                'name' => 'Oil Filter',
                'category_id' => 1,
                'brand_id' => 1,
                'location' => 'B3-C1',
                'description' => 'High quality oil filter',
                'quantity' => 0,
                'purchase_price' => 12.50,
                'selling_price' => 25.00
            ],
            [
                'part_number' => 'ELC-003',
                'name' => 'Spark Plugs (Set of 4)',
                'category_id' => 2,
                'brand_id' => 2,
                'location' => 'C2-D3',
                'description' => 'Premium spark plugs for better performance',
                'quantity' => 0,
                'purchase_price' => 28.00,
                'selling_price' => 55.00
            ]
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        echo "Sample products created successfully!\n";
    }
}
