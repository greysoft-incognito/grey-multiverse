<?php

namespace Database\Seeders;

use App\Models\Portal\Blog;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Blog::truncate();
        Blog::factory(15)->create();
    }
}
