<?php

namespace Tests;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

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
     * Setup up the Faker instance.
     *
     * @return void
     */
    protected function setUpFaker()
    {
        $this->faker = $this->makeFaker(config('app.faker_locale'));
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    protected function tearDown()
    {
        // Remove the files directory including any files uploaded during testing.
        Storage::cloud()->deleteDirectory('files');
        Storage::cloud()->deleteDirectory('reports');

        parent::tearDown();
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

    /**
     * @param \App\Models\User $user
     */
    protected function assertUserDisabled(User $user)
    {
        $this->assertDatabaseHas($user->getTable(), ['id' => $user->id]);
        $this->assertDatabaseMissing($user->getTable(), ['id' => $user->id, 'disabled_at' => null]);
    }
}
