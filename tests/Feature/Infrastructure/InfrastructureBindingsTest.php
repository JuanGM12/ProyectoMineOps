<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure;

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
}
