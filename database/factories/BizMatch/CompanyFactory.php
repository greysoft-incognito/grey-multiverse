<?php

namespace Database\Factories\BizMatch;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BizMatch\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Creates a user for each company
            'name' => $this->faker->company,
            'description' => $this->faker->paragraph,
            'industry_category' => $this->faker->randomElement([
                'Technology', 'Finance', 'Healthcare', 'Education', 'Retail', 'Manufacturing',
            ]),
            'location' => $this->faker->city.', '.$this->faker->country,
            'conference_objectives' => $this->faker->sentence,
        ];
    }
}
