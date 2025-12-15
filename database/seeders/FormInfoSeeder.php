<?php

namespace Database\Seeders;

use App\Models\Form;
use Illuminate\Database\Seeder;

class FormInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $form = Form::first();
        $form->infos()->insert([
            [
                'form_id' => $form->id,
                'title' => 'KADVINVEST 7.0',
                'subtitle' => 'BUILDING A RESILIENT ECONOMY',
                'content' => 'VENUE: UMARU MUSA YAR\'ADUA HALL, MURTALA SQUARE, KADUNA <br/> TIME: 9:00AM - 5:00PM',
                'list' => json_encode([
                    [
                        'title' => 'Sector Expo',
                        'content' => 'Thursday, 13th October 2022',
                    ], [
                        'title' => 'Commisioning of New Investments',
                        'content' => 'Friday, 14th October 2022',
                    ], [
                        'title' => 'Main Summit',
                        'content' => 'Saturday, 15th October 2022',
                    ], [
                        'title' => 'Gala Night',
                        'content' => 'Saturday, 15th October 2022',
                    ],
                ]),
                'icon' => null,
                'icon_color' => null,
                'increment_icon' => false,
                'image' => '',
                'template' => null,
                'position' => 'left',
                'type' => 'list',
            ], [
                'form_id' => $form->id,
                'title' => 'Register Now',
                'subtitle' => null,
                'content' => '',
                'list' => null,
                'icon' => null,
                'icon_color' => null,
                'increment_icon' => false,
                'image' => null,
                'template' => null,
                'position' => 'left',
                'type' => 'cta',
            ],
        ]);
    }
}
