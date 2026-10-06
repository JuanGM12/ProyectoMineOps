<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Activity\Commands\StartActivity;

use App\Application\Activity\Commands\StartActivity\StartActivityCommand;
use App\Application\Activity\Commands\StartActivity\StartActivityHandler;
use App\Application\Activity\Exceptions\ActivityNotFound;
use App\Application\Contracts\UnitOfWork;
use App\Domain\Activity\Entities\Activity;
use App\Domain\Activity\Enums\ActivityPriority;
use App\Domain\Activity\Enums\ActivityStatus;
use App\Domain\Activity\Repositories\ActivityRepository;
use App\Domain\Activity\ValueObjects\ActivityCode;
use App\Domain\Activity\ValueObjects\ActivityId;
use App\Domain\Activity\ValueObjects\ActivitySchedule;
use App\Domain\Activity\ValueObjects\ActivityTitle;
use App\Domain\Activity\ValueObjects\ResponsibleId;
use DateTimeImmutable;
use Mockery\MockInterface;
use Tests\TestCase;

final class StartActivityHandlerTest extends TestCase
{
    public function test_existing_activity_is_started_persisted_and_returned(): void
    {
        $activity = $this->activity();
        $repository = $this->mock(ActivityRepository::class, function (MockInterface $mock) use ($activity): void {
            $mock->shouldReceive('findById')
                ->once()
                ->withArgs(fn (ActivityId $id): bool => $id->value() === $activity->id()->value())
                ->andReturn($activity);
            $mock->shouldReceive('save')->once()->with($activity);
        });
        $handler = new StartActivityHandler($repository, $this->unitOfWork());

        $result = $handler->handle(new StartActivityCommand($activity->id()->value()));

        self::assertSame(ActivityStatus::IN_PROGRESS, $activity->status());
        self::assertSame(ActivityStatus::IN_PROGRESS->value, $result->status);
        self::assertSame($activity->id()->value(), $result->id);
    }

    public function test_missing_activity_is_rejected_without_persisting(): void
    {
        $activityId = '160210f7-ef17-4a37-886a-a7676626d0ec';
        $repository = $this->mock(ActivityRepository::class, function (MockInterface $mock): void {
            $mock->shouldReceive('findById')->once()->andReturnNull();
            $mock->shouldNotReceive('save');
        });
        $handler = new StartActivityHandler($repository, $this->unitOfWork());

        $this->expectException(ActivityNotFound::class);
        $this->expectExceptionMessage("No existe una actividad con id {$activityId}.");

        $handler->handle(new StartActivityCommand($activityId));
    }

    private function unitOfWork(): UnitOfWork
    {
        return $this->mock(UnitOfWork::class, function (MockInterface $mock): void {
            $mock->shouldReceive('transactional')
                ->once()
                ->andReturnUsing(fn (callable $operation): mixed => $operation());
        });
    }

    private function activity(): Activity
    {
        return Activity::create(
            new ActivityId('42a962db-b5da-4cc7-a20d-49436d5cc50f'),
            new ActivityCode('ACT-001'),
            new ActivityTitle('Inspección del frente norte'),
            'Validar las condiciones del área.',
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
