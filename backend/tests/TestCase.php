<?php

namespace Tests;

use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use Laravel\Lumen\Testing\DatabaseMigrations;

abstract class TestCase extends BaseTestCase
{
    use DatabaseMigrations;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @return void
     */
    public function runDatabaseMigrations()
    {
        // Use SQLite for testing
        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite.database', ':memory:');

        $this->artisan('migrate:fresh');
        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback');
        });
    }
}
