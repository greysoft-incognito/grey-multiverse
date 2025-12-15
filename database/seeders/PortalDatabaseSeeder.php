<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PortalDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            PortalSeeder::class,
            PortalPageSeeder::class,
            BlogSeeder::class,
            SectionSeeder::class,
            CardSeeder::class,
            SlidesSeeder::class,
            PortalFormSeeder::class,
            PortalGenericFormFieldSeeder::class,
            LearningPathSeeder::class,
        ]);
    }
}
