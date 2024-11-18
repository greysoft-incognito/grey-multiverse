<?php

namespace Tests;

use Database\Seeders\ConfigurationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (stripos($this->name(), 'Priority')) {
            $this->markTestSkipped('Temporarily Skipped!');
        }
    }

    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @return void
     */
    public function refreshDatabase()
    {
        if (config('database.default')) {
            $this->beforeRefreshingDatabase();

            if ($this->usingInMemoryDatabase()) {
                $this->restoreInMemoryDatabase();
            }

            $this->refreshTestDatabase();

            $this->afterRefreshingDatabase();
        }
    }

    /**
     * Perform any work that should take place once the database has finished refreshing.
     *
     * @return void
     */
    protected function afterRefreshingDatabase()
    {
        $this->seed();
        $this->seed(ConfigurationSeeder::class);
    }
}
