<?php

namespace Database\Seeders;

use App\Models\Portal\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        Section::truncate();
        Section::insert([
            // Sections for portal page 1
            [
                'portal_page_id' => 1,
                'title' => 'Hello Comrade',
                'title_highlight' => null,
                'subtitle' => 'We know getting a job is hard; not for the hotcakes.',
                'minititle' => '',
                'type' => 'banner',
                'image' => '',
                'image2' => null,
                'background' => null,
                'image_position' => 'right',
                'component' => 'Slider',
                'link' => json_encode(['title' => 'Join the Hotcake Community', 'url' => '#', 'target' => '_blank', 'type' => 'primary']),
                'content' => '',
                'video_link' => '',
                'list' => json_encode([]),
            ],
            [
                'portal_page_id' => 1,
                'title' => 'WHAT IS THE!',
                'title_highlight' => '',
                'subtitle' => 'Hotcake Community?',
                'minititle' => 'Techies and creatives combine; you\'re welcome to join the Hotcakes regardless of what type of skill you want to learn.',
                'type' => 'video',
                'image' => '',
                'image2' => null,
                'background' => null,
                'image_position' => 'right',
                'component' => 'About',
                'link' => json_encode(['title' => 'Get Started Today', 'url' => '#', 'target' => '_blank', 'type' => 'primary']),
                'content' => "Hotcake is a community space led by Greysoft Technologies for people who are looking for a rewarding job and the training to get them hired. Getting a job ike most things, can be a solitary endeavor, but if we can find the right support network, we can make it easier, more productive and more joyful. \n
                Here’s the opportunity: Stop talking about wanting to become a Product Designer, Fullstack Developer, Data Scientist, Digital Marketer and become one instead. Access training programs, one on one interview prep coaching and, free webinars. Simply learn, build your portfolio, get peer feedback, become a baked hot and attractive talent in the job market.",
                'video_link' => '#',
                'list' => json_encode([]),
            ], [
                'portal_page_id' => 1,
                'title' => 'Our HotCake Courses',
                'title_highlight' => null,
                'subtitle' => '',
                'minititle' => '',
                'type' => 'content',
                'image' => null,
                'image2' => null,
                'background' => null,
                'image_position' => 'right',
                'component' => 'Courses',
                'link' => json_encode([]),
                'content' => '',
                'video_link' => '',
                'list' => json_encode([]),
            ], [
                'portal_page_id' => 1,
                'title' => 'Join the Hotcakes.',
                'title_highlight' => null,
                'subtitle' => 'Let the baking begin',
                'minititle' => '',
                'type' => 'cards',
                'image' => null,
                'image2' => null,
                'background' => null,
                'image_position' => 'right',
                'component' => 'Cta',
                'link' => json_encode(['title' => 'Join Now', 'url' => 'https://t.me/+CEhAxL_QHaU3MGJk', 'target' => '_blank', 'type' => 'primary']),
                'content' => '',
                'video_link' => '',
                'list' => json_encode(['Professional Courses', 'One-On-One Coaching', 'Job Opportunities']),
            ],
        ]);
        Schema::enableForeignKeyConstraints();
    }
}
