<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Activity\Queries\GetActivities;

use App\Application\Activity\Contracts\ActivityReadRepository;
use App\Application\Activity\DTOs\ActivityDTO;
use App\Application\Activity\DTOs\PaginatedActivitiesDTO;
use App\Application\Activity\Queries\GetActivities\GetActivitiesHandler;
use App\Application\Activity\Queries\GetActivities\GetActivitiesQuery;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetActivitiesHandlerTest extends TestCase
{
    public function test_existing_activities_are_returned_as_paginated_dtos(): void
    {
        $repository = $this->mock(ActivityReadRepository::class, function (MockInterface $mock): void {
            $mock->shouldReceive('paginate')
                ->once()
                ->andReturn($this->page([
                    $this->activity('ACT-001', 'Inspección del frente norte'),
                    $this->activity('ACT-002', 'Revisión de ventilación'),
                ]));
        });
        $handler = new GetActivitiesHandler($repository);

        $result = $handler->handle(new GetActivitiesQuery);

        self::assertCount(2, $result->items);
        self::assertSame('ACT-001', $result->items[0]->code);
        self::assertSame('Inspección del frente norte', $result->items[0]->title);
        self::assertSame('ACT-002', $result->items[1]->code);
        self::assertSame('Revisión de ventilación', $result->items[1]->title);
    }

    public function test_no_activities_returns_an_empty_page(): void
    {
        $repository = $this->mock(ActivityReadRepository::class, function (MockInterface $mock): void {
            $mock->shouldReceive('paginate')->once()->andReturn($this->page([]));
        });
        $handler = new GetActivitiesHandler($repository);

        $result = $handler->handle(new GetActivitiesQuery);

        self::assertSame([], $result->items);
        self::assertSame(0, $result->total);
    }

    /** @param list<ActivityDTO> $items */
    private function page(array $items): PaginatedActivitiesDTO
    {
        return new PaginatedActivitiesDTO(
            items: $items,
            total: count($items),
            currentPage: 1,
            perPage: 15,
            lastPage: 1,
        );
    }

    private function activity(string $code, string $title): ActivityDTO
    {
        return new ActivityDTO(
            id: '00000000-0000-4000-8000-000000000001',
            code: $code,
            title: $title,
            description: 'Descripción de prueba',
            area: 'Operaciones',
            location: 'Frente norte',
            responsibleId: 'd7794c47-cb29-4f1b-ab36-e5f7568a52fe',
            priority: 'MEDIUM',
            status: 'PENDING',
            scheduledDate: '2026-10-10',
            dueDate: '2026-10-11',
        );
    }
}
