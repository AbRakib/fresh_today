<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class CategorySubcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $category = Category::firstOrCreate(
            [
                'name' => 'Uncategorized',
                'deleted' => 0,
            ],
            [
                'status' => 1,
            ],
        );

        Subcategory::firstOrCreate(
            [
                'category_id' => $category->id,
                'name' => 'Uncategorized',
                'deleted' => 0,
            ],
            [
                'status' => 1,
            ],
        );
    }
}
