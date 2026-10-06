<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Activity\Queries\GetActivityById;

use App\Application\Activity\Exceptions\ActivityNotFound;
use App\Application\Activity\Queries\GetActivityById\GetActivityByIdHandler;
use App\Application\Activity\Queries\GetActivityById\GetActivityByIdQuery;
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

final class GetActivityByIdHandlerTest extends TestCase
{
    private const ACTIVITY_ID = '2ee65fcf-a776-4d49-afc1-a11e38b877de';

    public function test_existing_activity_is_returned_as_dto(): void
    {
        $repository = $this->mock(ActivityRepository::class, function (MockInterface $mock): void {
            $mock->shouldReceive('findById')
                ->once()
                ->withArgs(fn (ActivityId $id): bool => $id->value() === self::ACTIVITY_ID)
                ->andReturn($this->activity());
        });
        $handler = new GetActivityByIdHandler($repository);

        $result = $handler->handle(new GetActivityByIdQuery(self::ACTIVITY_ID));

        self::assertSame(self::ACTIVITY_ID, $result->id);
        self::assertSame('ACT-001', $result->code);
        self::assertSame('Inspección del frente norte', $result->title);
        self::assertSame('PENDING', $result->status);
    }

    public function test_unknown_activity_is_rejected(): void
    {
        $repository = $this->mock(ActivityRepository::class, function (MockInterface $mock): void {
            $mock->shouldReceive('findById')
                ->once()
                ->withArgs(fn (ActivityId $id): bool => $id->value() === self::ACTIVITY_ID)
                ->andReturnNull();
        });
        $handler = new GetActivityByIdHandler($repository);

        $this->expectException(ActivityNotFound::class);
        $this->expectExceptionMessage(self::ACTIVITY_ID);

        $handler->handle(new GetActivityByIdQuery(self::ACTIVITY_ID));
    }

    private function activity(): Activity
    {
        return Activity::create(
            new ActivityId(self::ACTIVITY_ID),
            new ActivityCode('ACT-001'),
            new ActivityTitle('Inspección del frente norte'),
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
