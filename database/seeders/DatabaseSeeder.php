<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (Role::count() < 1 && Permission::count() < 1) {
            $this->command->call('app:sync-roles');
        }

        // User::factory(10)->create();

        \App\Models\User::factory(1, [
            'privileges' => ['admin'],
            'email' => 'admin@greysoft.ng',
        ])->create([
            'privileges' => ['admin'],
            'email' => 'admin@greysoft.ng',
        ]);

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call([
            ConfigurationSeeder::class,
            FormSeeder::class,
            FormInfoSeeder::class,
            FormFieldSeeder::class,
            CompanySeeder::class,
            PortalDatabaseSeeder::class,
        ]);
    }
}
