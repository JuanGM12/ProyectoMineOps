<?php

namespace App\Providers;

use App\Application\Activity\Bus\CommandBus;
use App\Application\Activity\Bus\MappedCommandBus;
use App\Application\Activity\Bus\MappedQueryBus;
use App\Application\Activity\Bus\QueryBus;
use App\Application\Activity\Commands\CancelActivity\CancelActivityCommand;
use App\Application\Activity\Commands\CancelActivity\CancelActivityHandler;
use App\Application\Activity\Commands\CompleteActivity\CompleteActivityCommand;
use App\Application\Activity\Commands\CompleteActivity\CompleteActivityHandler;
use App\Application\Activity\Commands\CreateActivity\CreateActivityCommand;
use App\Application\Activity\Commands\CreateActivity\CreateActivityHandler;
use App\Application\Activity\Commands\StartActivity\StartActivityCommand;
use App\Application\Activity\Commands\StartActivity\StartActivityHandler;
use App\Application\Activity\Commands\UpdateActivity\UpdateActivityCommand;
use App\Application\Activity\Commands\UpdateActivity\UpdateActivityHandler;
use App\Application\Activity\Contracts\ActivityReadRepository;
use App\Application\Activity\Queries\GetActivities\GetActivitiesHandler;
use App\Application\Activity\Queries\GetActivities\GetActivitiesQuery;
use App\Application\Activity\Queries\GetActivityById\GetActivityByIdHandler;
use App\Application\Activity\Queries\GetActivityById\GetActivityByIdQuery;
use App\Application\Contracts\UnitOfWork;
use App\Domain\Activity\Repositories\ActivityRepository;
use App\Infrastructure\Persistence\EloquentActivityReadRepository;
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
        $this->app->bind(ActivityReadRepository::class, EloquentActivityReadRepository::class);
        $this->app->bind(UnitOfWork::class, LaravelUnitOfWork::class);

        $this->app->singleton(CommandBus::class, fn (): MappedCommandBus => new MappedCommandBus([
            CreateActivityCommand::class => fn (CreateActivityCommand $command) => $this->app->make(CreateActivityHandler::class)->handle($command),
            StartActivityCommand::class => fn (StartActivityCommand $command) => $this->app->make(StartActivityHandler::class)->handle($command),
            CompleteActivityCommand::class => fn (CompleteActivityCommand $command) => $this->app->make(CompleteActivityHandler::class)->handle($command),
            CancelActivityCommand::class => fn (CancelActivityCommand $command) => $this->app->make(CancelActivityHandler::class)->handle($command),
            UpdateActivityCommand::class => fn (UpdateActivityCommand $command) => $this->app->make(UpdateActivityHandler::class)->handle($command),
        ]));

        $this->app->singleton(QueryBus::class, fn (): MappedQueryBus => new MappedQueryBus([
            GetActivitiesQuery::class => fn (GetActivitiesQuery $query) => $this->app->make(GetActivitiesHandler::class)->handle($query),
            GetActivityByIdQuery::class => fn (GetActivityByIdQuery $query) => $this->app->make(GetActivityByIdHandler::class)->handle($query),
        ]));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
