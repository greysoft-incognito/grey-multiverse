<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\Portal\LearningPath;
use App\Models\Portal\Portal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class LearningPathSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        LearningPath::truncate();
        Portal::get()->map(function ($portal) {
            $portal->reg_form && $portal->reg_form->learning_paths()->insert([
                [
                    'learnable_type' => Form::class,
                    'learnable_id' => $portal->reg_form->id,
                    'description' => 'Product Design',
                    'subtitle' => 'Product Design',
                    'title' => 'Product Design',
                    'slug' => str('Product Design')->slug(),
                    'image' => asset('images/pe/design.png'),
                    'video' => '',
                    'background' => '',
                    'component' => '',
                    'video_link' => 'https://www.youtube.com/watch?v=MCy6WtYjI-8&feature=emb_imp_woyt',
                    'price' => 115000.00,
                ],
                [
                    'learnable_type' => Form::class,
                    'learnable_id' => $portal->reg_form->id,
                    'description' => 'Frontend Engineering',
                    'subtitle' => 'Frontend Engineering',
                    'title' => 'Frontend Engineering',
                    'slug' => str('Frontend Engineering')->slug(),
                    'image' => asset('images/pe/frontend.png'),
                    'video' => '',
                    'background' => '',
                    'component' => '',
                    'video_link' => 'https://www.youtube.com/embed/3cEhOYvO9SM"',
                    'price' => 115000.00,
                ],
                [
                    'learnable_type' => Form::class,
                    'learnable_id' => $portal->reg_form->id,
                    'description' => 'Backend Engineering',
                    'subtitle' => 'Backend Engineering',
                    'title' => 'Backend Engineering',
                    'slug' => str('Backend Engineering')->slug(),
                    'image' => asset('images/pe/backend.png'),
                    'video' => '',
                    'background' => '',
                    'component' => '',
                    'video_link' => 'https://www.youtube.com/embed/9IczM7y0Yc8',
                    'price' => 115000.00,
                ],
                [
                    'learnable_type' => Form::class,
                    'learnable_id' => $portal->reg_form->id,
                    'description' => 'Data Science',
                    'subtitle' => 'Data Science',
                    'title' => 'Data Science',
                    'slug' => str('Data Science')->slug(),
                    'image' => asset('images/pe/data.png'),
                    'video' => '',
                    'background' => '',
                    'component' => '',
                    'video_link' => 'https://www.youtube.com/embed/NJvncopOC88',
                    'price' => 115000.00,
                ],
                [
                    'learnable_type' => Form::class,
                    'learnable_id' => $portal->reg_form->id,
                    'description' => 'Digital Marketing',
                    'subtitle' => 'Digital Marketing',
                    'title' => 'Digital Marketing',
                    'slug' => str('Digital Marketing')->slug(),
                    'image' => asset('images/pe/digital.png'),
                    'video' => '',
                    'background' => '',
                    'component' => '',
                    'video_link' => 'https://www.youtube.com/embed/FVOfhCxJ6DU',
                    'price' => 50000.00,
                ],
                [
                    'learnable_type' => Form::class,
                    'learnable_id' => $portal->reg_form->id,
                    'description' => 'Blockchan Technology',
                    'subtitle' => 'Blockchan Technology',
                    'title' => 'Blockchan Technology',
                    'slug' => str('Blockchan Technology')->slug(),
                    'image' => asset('images/pe/blockchain.png'),
                    'video' => '',
                    'background' => '',
                    'component' => '',
                    'video_link' => null,
                    'price' => 115000.00,
                ],
            ]);
        });
        Schema::enableForeignKeyConstraints();
    }
}
