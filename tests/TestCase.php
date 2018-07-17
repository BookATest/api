<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;
    use WithFaker;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        // Run seeds.
        Artisan::call('db:seed');

        // Set cache prefix.
        Config::set('cache.prefix', 'testing');

        // Set the log path.
        Config::set('log.channels.single.path', storage_path('logs/testing.log'));

        // Clear the cache.
        Artisan::call('cache:clear');
    }
}
