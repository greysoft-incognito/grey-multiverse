<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\Portal\Portal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PortalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        Portal::truncate();
        Portal::insert([
            [
                'name' => 'GreyAcademy',
                'slug' => 'greyacademy',
                'description' => 'We know getting a job is hard; not for the hotcakes.',
                'footer_info' => '',
                'logo' => '',
                'favicon' => '',
                'allow_registration' => true,
                'registration_model' => Guest::class,
                'reg_fee' => 20000,
                'reg_link_title' => 'Enroll Now',
                'login_link_title' => 'Login',
                'address' => '31 Gwarri avenue, Barnawa, Kaduna',
                'email' => 'hi@greysoft.ng',
                'phone' => '09025234813',
                'meta' => 'We know getting a job is hard; not for the hotcakes.',
                'banner' => asset('images/pe/mul.png'),
                'footer_groups' => json_encode([
                    'company', 'resources', 'about',
                ]),
                'socials' => json_encode([
                    ['type' => 'telegram', 'link' => 'https://t.me/+CEhAxL_QHaU3MGJk'],
                    ['type' => 'twitter', 'link' => 'https://twitter.com/greyhobb'],
                    ['type' => 'instagram', 'link' => 'https://www.instagram.com/greyhobb/'],
                    ['type' => 'linkedin', 'link' => 'https://www.linkedin.com/company/greysoft-tech/mycompany/'],
                ]),
                'copyright' => '© {year} GreyAcademy, Nigeria. All rights reserved. All right reserved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        Schema::enableForeignKeyConstraints();
    }
}
