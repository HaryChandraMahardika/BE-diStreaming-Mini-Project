<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Post;

class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //Buat 5 kategories
        $categories = Category::factory(5)->create();

        //Buat 20 post dengan kategori random
        foreach ($categories as $category) {
            Post::factory(4)->create([
                'category_id' => $category->id,
            ]);
        }
    }
}
