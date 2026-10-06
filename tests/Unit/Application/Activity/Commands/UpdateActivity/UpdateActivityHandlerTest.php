<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Activity\Commands\UpdateActivity;

use App\Application\Activity\Commands\UpdateActivity\UpdateActivityCommand;
use App\Application\Activity\Commands\UpdateActivity\UpdateActivityHandler;
use App\Application\Contracts\UnitOfWork;
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

final class UpdateActivityHandlerTest extends TestCase
{
    public function test_existing_activity_is_updated_atomically_and_returned(): void
    {
        $activity = $this->activity();
        $repository = $this->mock(ActivityRepository::class, function (MockInterface $mock) use ($activity): void {
            $mock->shouldReceive('findById')->once()->andReturn($activity);
            $mock->shouldReceive('save')->once()->with($activity);
        });
        $unitOfWork = $this->mock(UnitOfWork::class, function (MockInterface $mock): void {
            $mock->shouldReceive('transactional')
                ->once()
                ->andReturnUsing(fn (callable $operation): mixed => $operation());
        });
        $handler = new UpdateActivityHandler($repository, $unitOfWork);

        $result = $handler->handle(new UpdateActivityCommand(
            activityId: $activity->id()->value(),
            title: 'Actividad crítica actualizada',
            description: null,
            area: 'Seguridad',
            location: 'Frente sur',
            responsibleId: 'eb5a466f-f69d-4b69-a727-5dcb52fe9e39',
            priority: ActivityPriority::CRITICAL,
            scheduledDate: new DateTimeImmutable('2026-10-12'),
            dueDate: new DateTimeImmutable('2026-10-14'),
        ));

        self::assertSame('Actividad crítica actualizada', $result->title);
        self::assertSame('CRITICAL', $result->priority);
        self::assertSame('2026-10-12', $result->scheduledDate);
        self::assertSame('2026-10-14', $result->dueDate);
        self::assertSame('eb5a466f-f69d-4b69-a727-5dcb52fe9e39', $result->responsibleId);
    }

    private function activity(): Activity
    {
        return Activity::create(
            new ActivityId('00000000-0000-4000-8000-000000000001'),
            new ActivityCode('ACT-001'),
            new ActivityTitle('Actividad inicial'),
            'Descripción inicial',
            'Operaciones',
            'Frente norte',
            new ResponsibleId('d7794c47-cb29-4f1b-ab36-e5f7568a52fe'),
            ActivityPriority::LOW,
            new ActivitySchedule(new DateTimeImmutable('2026-10-10'), null),
        );
    }
}
