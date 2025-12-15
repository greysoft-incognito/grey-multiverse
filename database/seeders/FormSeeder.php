<?php

namespace Database\Seeders;

use App\Models\Form;
use Illuminate\Database\Seeder;

class FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($i = Form::first()) {
            $i->delete();
        }
        Form::insert([
            [
                'name' => 'KADVINVEST 7.0',
                'slug' => str('KADVINVEST 7.0')->slug(),
                'title' => 'BUILDING A RESILIENT ECONOMY',
                'logo' => 'http://kadinvest.live/imgs/header/Kadinvest.png',
                'banner' => 'http://kadinvest.live/imgs/header/theme.png',
                'banner_title' => 'KADVINVEST 7.0',
                'banner_info' => 'BUILDING A RESILIENT ECONOMY',
                'deadline' => '2022-10-13',
                'failure_message' => 'Hello :fullname, Unfortunattely we could not submit your form.',
                'success_message' => 'Hello :fullname, This is to confirm your submssion of this form was successfull.
                Here is your QR Code :qrcode.',
                'socials' => json_encode([
                    'facebook' => 'https://web.facebook.com/InvestKaduna?_rdc=1&_rdr',
                    'twitter' => 'https://twitter.com/InvestKaduna?ref_src=twsrc%5Egoogle%7Ctwcamp%5Eserp%7Ctwgr%5Eauthor',
                    'instagram' => 'https://www.instagram.com/accounts/login/?next=/investkaduna/',
                    'linkedin' => 'https://ng.linkedin.com/company/kadipa',
                ]),
            ],
        ]);
    }
}
