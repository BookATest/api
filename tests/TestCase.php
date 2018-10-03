<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;

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

        // Set cache prefix.
        Config::set('cache.prefix', 'testing');

        // Set the log path.
        Config::set('logging.channels.single.path', storage_path('logs/testing.log'));
    }

    /**
     * Fakes all events with exception of model events.
     */
    protected function fakeEvents()
    {
        $initialDispatcher = Event::getFacadeRoot();
        Event::fake();
        Model::setEventDispatcher($initialDispatcher);
    }

    /**
     * @param string $eventClass
     * @param callable|null $callback
     */
    protected function assertEventDispatched(string $eventClass, callable $callback = null)
    {
        Event::assertDispatched($eventClass, function ($event) use ($callback) {
            if ($callback) {
                $callback($event);
            }

            return true;
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    protected function assertModelDeleted(Model $model)
    {
        $this->assertDatabaseMissing($model->getTable(), ['id' => $model->id]);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    protected function assertModelSoftDeleted(Model $model)
    {
        $this->assertDatabaseHas($model->getTable(), ['id' => $model->id]);
        $this->assertDatabaseMissing($model->getTable(), ['id' => $model->id, 'deleted_at' => null]);
    }
}
