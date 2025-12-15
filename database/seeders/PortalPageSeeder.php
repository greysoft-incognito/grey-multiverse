<?php

namespace Database\Seeders;

use App\Models\Portal\PortalPage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PortalPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        PortalPage::truncate();
        PortalPage::insert([
            [
                'portal_id' => 1,
                'slug' => 'welcome',
                'title' => 'GreyAcademy',
                'description' => 'We know getting a job is hard; not for the hotcakes.',
                'keywords' => 'We know getting a job is hard; not for the hotcakes.',
                'content' => 'We know getting a job is hard; not for the hotcakes.',
                'footer_group' => 'company',
                'component' => 'Portal3',
                'image' => null,
                'index' => true,
                'in_footer' => true,
                'in_navbar' => false,
            ], [
                'portal_id' => 1,
                'slug' => str('Hire our grads')->slug(),
                'title' => 'Hire Our Grads',
                'description' => 'Assertively parallel task synergistic deliverables after high-quality.',
                'keywords' => 'Hire our grads',
                'content' => fake()->paragraphs(3, true),
                'footer_group' => 'company',
                'component' => 'Portal3',
                'image' => asset('images/pe/depertment-1.png'),
                'index' => false,
                'in_footer' => true,
                'in_navbar' => false,
            ], [
                'portal_id' => 1,
                'slug' => str('Colaborate with us')->slug(),
                'title' => 'Colaborate With Us',
                'description' => 'Assertively parallel task synergistic deliverables after high-quality.',
                'keywords' => 'Colaborate with us',
                'content' => fake()->paragraphs(3, true),
                'footer_group' => 'company',
                'component' => 'Portal3',
                'image' => asset('images/pe/depertment-2.png'),
                'index' => false,
                'in_footer' => true,
                'in_navbar' => false,
            ], [
                'portal_id' => 1,
                'slug' => str('Privacy Policy')->slug(),
                'title' => 'Privacy Policy',
                'description' => 'Assertively parallel task synergistic deliverables after high-quality.',
                'keywords' => 'Privacy Policy',
                'content' => fake()->paragraphs(3, true),
                'footer_group' => 'resources',
                'component' => 'Portal3',
                'image' => asset('images/pe/depertment-3.png'),
                'index' => false,
                'in_footer' => true,
                'in_navbar' => false,
            ], [
                'portal_id' => 1,
                'slug' => str('FAQ')->slug(),
                'title' => 'FAQ',
                'description' => 'Assertively parallel task synergistic deliverables after high-quality.',
                'keywords' => 'FAQ',
                'content' => fake()->paragraphs(3, true),
                'footer_group' => 'resources',
                'component' => 'Portal3',
                'image' => asset('images/pe/depertment-3.png'),
                'index' => false,
                'in_footer' => true,
                'in_navbar' => false,
            ], [
                'portal_id' => 1,
                'slug' => str('Our Story')->slug(),
                'title' => 'Our Story',
                'description' => 'Assertively parallel task synergistic deliverables after high-quality.',
                'keywords' => 'Our Story',
                'content' => fake()->paragraphs(3, true),
                'footer_group' => 'about',
                'component' => 'Portal3',
                'image' => asset('images/pe/depertment-3.png'),
                'index' => false,
                'in_footer' => true,
                'in_navbar' => false,
            ], [
                'portal_id' => 1,
                'slug' => str('Blog')->slug(),
                'title' => 'Blog',
                'description' => 'Assertively parallel task synergistic deliverables after high-quality.',
                'keywords' => 'Blog',
                'content' => fake()->paragraphs(3, true),
                'footer_group' => 'about',
                'component' => 'Portal3',
                'image' => asset('images/pe/depertment-3.png'),
                'index' => false,
                'in_footer' => true,
                'in_navbar' => false,
            ],
        ]);
        Schema::enableForeignKeyConstraints();
    }
}
