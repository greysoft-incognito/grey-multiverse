<?php

namespace Database\Factories\Portal;

use App\Models\Portal\Portal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Portal\Blog>
 */
class BlogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $title = $this->faker->words(5, true);
        $portal = Portal::inRandomOrder()->first();

        return [
            'user_id' => 1,
            'portal_id' => $portal->id ?? 1,
            'slug' => str($title)->slug(),
            'title' => $title,
            'subtitle' => $this->faker->words(10, true),
            'image' => random_img('images/pe'),
            'content' => $this->faker->paragraphs(5, true),
        ];
    }
}
