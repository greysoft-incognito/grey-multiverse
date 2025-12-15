<?php

namespace Database\Factories\v1\Portal;

use Illuminate\Database\Eloquent\Factories\Factory;
use V1\Models\Portal\Portal;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\V1\Models\Portal\Blog>
 */
class BlogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \V1\Models\Portal\Blog::class;

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
