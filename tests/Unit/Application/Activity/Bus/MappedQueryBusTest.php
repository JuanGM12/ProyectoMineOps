<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Activity\Bus;

use App\Application\Activity\Bus\MappedQueryBus;
use App\Application\Activity\Bus\Query;
use App\Application\Activity\Bus\QueryHandlerNotFound;
use Tests\TestCase;

final class MappedQueryBusTest extends TestCase
{
    public function test_registered_handler_receives_query_and_returns_result(): void
    {
        $query = new class('activity-id') implements Query
        {
            public function __construct(public readonly string $activityId) {}
        };
        $queryBus = new MappedQueryBus([
            $query::class => fn (Query $received): string => $received->activityId,
        ]);

        $result = $queryBus->ask($query);

        self::assertSame('activity-id', $result);
    }

    public function test_unregistered_query_is_rejected(): void
    {
        $query = new class implements Query {};
        $queryBus = new MappedQueryBus([]);

        $this->expectException(QueryHandlerNotFound::class);
        $this->expectExceptionMessage($query::class);

        $queryBus->ask($query);
    }
}
