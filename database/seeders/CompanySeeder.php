<?php

namespace Database\Seeders;

use App\Models\BizMatch\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::factory()->count(20)->create();
    }
}
