<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia;

class TestingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (!$this->app->runningUnitTests()) {
            return;
        }

        AssertableInertia::macro('hasResource', function (string $key, JsonResource $jsonResource) {
            /**
             * @var AssertableInertia $this
             */
            $this->has($key);

            expect($this->prop($key))->toEqual($jsonResource->response()->getData(true));

            return $this;
        });

        AssertableInertia::macro('hasPaginatedResource', function (string $key, ResourceCollection $jsonResource) {
            /**
             * @var AssertableInertia $this
             */
            $this->hasResource("{$key}.data", $jsonResource);

            expect($this->prop($key))->toHaveKeys(['data', 'links', 'meta']);

            return $this;
        });

        TestResponse::macro('assertHasResource', function (string $key, JsonResource $jsonResource) {
            /**
             * @var AssertableInertia $this
             */
            return $this->assertInertia(fn (AssertableInertia $assertableInertia) => $assertableInertia->hasResource($key, $jsonResource));
        });

        TestResponse::macro('assertHasPaginatedResource', function (string $key, ResourceCollection $jsonResource) {
            /**
             * @var AssertableInertia $this
             */
            return $this->assertInertia(fn (AssertableInertia $assertableInertia) => $assertableInertia->hasPaginatedResource($key, $jsonResource));
        });

        TestResponse::macro('assertComponent', function (string $component) {
            /**
             * @var AssertableInertia $this
             */
            return $this->assertInertia(fn (AssertableInertia $assertableInertia) => $assertableInertia->component($component, true));
        });
    }
}
