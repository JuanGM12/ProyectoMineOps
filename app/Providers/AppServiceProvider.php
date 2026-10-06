<?php

namespace App\Providers;

use App\Application\Contracts\UnitOfWork;
use App\Domain\Activity\Repositories\ActivityRepository;
use App\Infrastructure\Persistence\EloquentActivityRepository;
use App\Infrastructure\Persistence\LaravelUnitOfWork;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ActivityRepository::class, EloquentActivityRepository::class);
        $this->app->bind(UnitOfWork::class, LaravelUnitOfWork::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
