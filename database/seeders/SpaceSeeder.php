<?php

namespace Database\Seeders;

use App\Models\Space;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class SpaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        Space::truncate();
        $spaces = [];
        $letters = ['A', 'B', 'C', 'D'];
        $colors = ['yellow', 'blue', 'teal', 'green'];
        foreach ($letters as $k => $letter) {
            foreach (range(1, 7) as $key => $range) {
                $spaces[] = [
                    'name' => $letter.$range,
                    'size' => '0',
                    'info' => 'About this space: '.$letter.$range,
                    'price' => 0,
                    'data' => json_encode(['color' => $colors[$k]]),
                ];
            }
        }

        Space::insert($spaces);
        Schema::enableForeignKeyConstraints();
    }
}
