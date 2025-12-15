<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class CheckDuplicateRoutes extends Command
{
    protected $signature = 'routes:duplicates';

    protected $description = 'Find duplicate route names in Laravel';

    public function handle()
    {
        $routes = collect(Route::getRoutes())->mapWithKeys(fn ($route) => [
            $route->getName() => $route->uri(),
        ])->filter(fn ($_, $name) => ! is_null($name));

        $duplicates = $routes->duplicates(null, true);

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate route names found.');
        } else {
            $this->error('Duplicate route names found:');
            foreach ($duplicates as $name => $uri) {
                $this->line(" - {$name}: {$uri}");
            }
        }
    }
}
