<?php

namespace Tests\Feature;

use App\Models\Configuration;
use App\Models\User;
use Illuminate\Foundation\Testing\Concerns\InteractsWithConsole;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ConfigTest extends TestCase
{
    use InteractsWithConsole;

    public function testCanLoadConfig(): void
    {
        $user = User::first();

        $this->artisan('app:sync-roles');
        $user->syncRoles(config('permission-defs.roles', []));
        $user->syncPermissions(config('permission-defs.permissions', []));

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->get('/api/admin/configurations');

        $response->assertStatus(200);
    }

    public function testCanLoadAParticularConfig(): void
    {
        $user = User::first();

        $this->artisan('app:sync-roles');
        $user->syncRoles(config('permission-defs.roles', []));
        $user->syncPermissions(config('permission-defs.permissions', []));

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->get('/api/admin/configurations/app_name');

        $response->assertStatus(200);
    }

    public function testCanSaveConfig(): void
    {
        $user = User::first();

        $this->artisan('app:sync-roles');
        $user->syncRoles(config('permission-defs.roles', []));
        $user->syncPermissions(config('permission-defs.permissions', []));

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->post('/api/admin/configurations', [
            'app_name' => 'Test App',
        ]);

        $response->assertStatus(202);
    }

    public function testCanSaveFileConfigs(): void
    {
        $user = User::first();

        $this->artisan('app:sync-roles');
        $user->syncRoles(config('permission-defs.roles', []));
        $user->syncPermissions(config('permission-defs.permissions', []));

        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->post('/api/admin/configurations', [
            'app_logo' => UploadedFile::fake()->image('logo.jpg'),
        ]);

        $conf = Configuration::where('key', 'app_logo')->first();
        $this->assertStringContainsString('.jpg', $conf->value);
        $this->assertStringEndsNotWith('default.png', $conf->value);

        $response->assertStatus(202);
    }
}
