<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Activity\Queries\GetActivities;

use App\Application\Activity\Queries\GetActivities\GetActivitiesHandler;
use App\Application\Activity\Queries\GetActivities\GetActivitiesQuery;
use App\Domain\Activity\Entities\Activity;
use App\Domain\Activity\Enums\ActivityPriority;
use App\Domain\Activity\Repositories\ActivityRepository;
use App\Domain\Activity\ValueObjects\ActivityCode;
use App\Domain\Activity\ValueObjects\ActivityId;
use App\Domain\Activity\ValueObjects\ActivitySchedule;
use App\Domain\Activity\ValueObjects\ActivityTitle;
use App\Domain\Activity\ValueObjects\ResponsibleId;
use DateTimeImmutable;
use Mockery\MockInterface;
use Tests\TestCase;

final class GetActivitiesHandlerTest extends TestCase
{
    public function test_existing_activities_are_returned_as_dtos(): void
    {
        $repository = $this->mock(ActivityRepository::class, function (MockInterface $mock): void {
            $mock->shouldReceive('findAll')
                ->once()
                ->andReturn([
                    $this->activity('ACT-001', 'Inspección del frente norte'),
                    $this->activity('ACT-002', 'Revisión de ventilación'),
                ]);
        });
        $handler = new GetActivitiesHandler($repository);

        $result = $handler->handle(new GetActivitiesQuery);

        self::assertCount(2, $result);
        self::assertSame('ACT-001', $result[0]->code);
        self::assertSame('Inspección del frente norte', $result[0]->title);
        self::assertSame('ACT-002', $result[1]->code);
        self::assertSame('Revisión de ventilación', $result[1]->title);
    }

    public function test_no_activities_returns_an_empty_list(): void
    {
        $repository = $this->mock(ActivityRepository::class, function (MockInterface $mock): void {
            $mock->shouldReceive('findAll')->once()->andReturn([]);
        });
        $handler = new GetActivitiesHandler($repository);

        $result = $handler->handle(new GetActivitiesQuery);

        self::assertSame([], $result);
    }

    private function activity(string $code, string $title): Activity
    {
        return Activity::create(
            ActivityId::generate(),
            new ActivityCode($code),
            new ActivityTitle($title),
            'Descripción de prueba',
            'Operaciones',
            'Frente norte',
            new ResponsibleId('d7794c47-cb29-4f1b-ab36-e5f7568a52fe'),
            ActivityPriority::MEDIUM,
            new ActivitySchedule(
                new DateTimeImmutable('2026-10-10'),
                new DateTimeImmutable('2026-10-11'),
            ),
        );
    }
}
