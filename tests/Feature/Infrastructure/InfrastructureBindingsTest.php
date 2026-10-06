<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure;

use App\Application\Activity\Bus\CommandBus;
use App\Application\Activity\Bus\MappedCommandBus;
use App\Application\Activity\Bus\MappedQueryBus;
use App\Application\Activity\Bus\QueryBus;
use App\Application\Activity\Commands\CreateActivity\CreateActivityHandler;
use App\Application\Activity\Queries\GetActivities\GetActivitiesHandler;
use App\Application\Contracts\UnitOfWork;
use App\Domain\Activity\Repositories\ActivityRepository;
use App\Infrastructure\Persistence\EloquentActivityRepository;
use App\Infrastructure\Persistence\LaravelUnitOfWork;
use Tests\TestCase;

final class InfrastructureBindingsTest extends TestCase
{
    public function test_service_container_binds_internal_contracts_to_infrastructure(): void
    {
        self::assertInstanceOf(
            EloquentActivityRepository::class,
            $this->app->make(ActivityRepository::class),
        );
        self::assertInstanceOf(
            LaravelUnitOfWork::class,
            $this->app->make(UnitOfWork::class),
        );
    }

    public function test_service_container_resolves_mediator_buses_and_handlers(): void
    {
        self::assertInstanceOf(MappedCommandBus::class, $this->app->make(CommandBus::class));
        self::assertInstanceOf(MappedQueryBus::class, $this->app->make(QueryBus::class));
        self::assertInstanceOf(CreateActivityHandler::class, $this->app->make(CreateActivityHandler::class));
        self::assertInstanceOf(GetActivitiesHandler::class, $this->app->make(GetActivitiesHandler::class));
    }
}
