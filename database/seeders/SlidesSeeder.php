<?php

namespace Database\Seeders;

use App\Models\Portal\Section;
use App\Models\Portal\Slider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class SlidesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        Slider::truncate();
        Section::whereComponent('Slider')->whereType('banner')->get()->each(function ($section) {
            $section->sliders()->saveMany([
                new Slider([
                    // 'section_id' => '',
                    'title' => 'Hello Comrade',
                    'subtitle' => 'We know getting a job is hard; not for the hotcakes.',
                    'content' => '',
                    'image' => asset('images/pe/joshchief.png'),
                    'link' => ['title' => 'Join The Hotcake Community', 'url' => 'https://t.me/+CEhAxL_QHaU3MGJk', 'target' => '_blank', 'type' => 'primary'],
                    'list' => ['Wait, she’s offline.', 'Make i call her'],
                    'component' => '',
                ]),
                new Slider([
                    // 'section_id' => '',
                    'title' => 'Hello Comrade',
                    'subtitle' => 'We know getting a job is hard; not for the hotcakes.',
                    'content' => '',
                    'image' => asset('images/pe/emmanuel.png'),
                    'link' => ['title' => 'Join The Hotcake Community', 'url' => 'https://t.me/+CEhAxL_QHaU3MGJk', 'target' => '_blank', 'type' => 'primary'],
                    'list' => ['How far @gami, u get the job?.', 'You no give us gist', 'How was the interview?'],
                    'component' => '',
                ]),
                new Slider([
                    // 'section_id' => '',
                    'title' => 'Hello Comrade',
                    'subtitle' => 'We know getting a job is hard; not for the hotcakes.',
                    'content' => '',
                    'image' => asset('images/pe/mariya.png'),
                    'link' => ['title' => 'Join The Hotcake Community', 'url' => 'https://t.me/+CEhAxL_QHaU3MGJk', 'target' => '_blank', 'type' => 'primary'],
                    'list' => [
                        'Wow, so @gami had an interview?',
                        'Abeg give us gist',
                    ],
                    'component' => '',
                ]),
                new Slider([
                    // 'section_id' => '',
                    'title' => 'Hello Comrade',
                    'subtitle' => 'We know getting a job is hard; not for the hotcakes.',
                    'content' => '',
                    'image' => asset('images/pe/helen.png'),
                    'link' => ['title' => 'Join The Hotcake Community', 'url' => 'https://t.me/+CEhAxL_QHaU3MGJk', 'target' => '_blank', 'type' => 'primary'],
                    'list' => [
                        'lol, Yes i did ooo.',
                        '... and it was awesome😀',
                        'Honestly guys i am glad to be part of the HOTCAKE community',
                    ],
                    'component' => '',
                ]),
                new Slider([
                    // 'section_id' => '',
                    'title' => 'Hello Comrade',
                    'subtitle' => 'We know getting a job is hard; not for the hotcakes.',
                    'content' => '',
                    'image' => asset('images/pe/bilal.png'),
                    'link' => ['title' => 'Join The Hotcake Community', 'url' => 'https://t.me/+CEhAxL_QHaU3MGJk', 'target' => '_blank', 'type' => 'primary'],
                    'list' => [
                        'Omoh @gami just finished her course last week.',
                        '...and she’s bagged a job in no time',
                        'Sweet!!!',
                    ],
                    'component' => '',
                ]),
                new Slider([
                    // 'section_id' => '',
                    'title' => 'Hello Comrade',
                    'subtitle' => 'We know getting a job is hard; not for the hotcakes.',
                    'content' => '',
                    'image' => asset('images/pe/mike.png'),
                    'link' => ['title' => 'Join The Hotcake Community', 'url' => 'https://t.me/+CEhAxL_QHaU3MGJk', 'target' => '_blank', 'type' => 'primary'],
                    'list' => [
                        'There’s a reason we are called the HOTCAKES 😀',
                        'Na them dey rush us',
                    ],
                    'component' => '',
                ]),
            ]);
        });
    }
}
