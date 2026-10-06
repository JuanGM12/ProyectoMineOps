<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Persistence;

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
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use RuntimeException;
use Tests\TestCase;

final class LaravelUnitOfWorkTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_returns_the_result_of_a_successful_transaction(): void
    {
        $unitOfWork = $this->app->make(UnitOfWork::class);

        $result = $unitOfWork->transactional(static fn (): string => 'committed');

        self::assertSame('committed', $result);
    }

    public function test_it_rolls_back_changes_and_propagates_the_exception(): void
    {
        $repository = $this->app->make(ActivityRepository::class);
        $unitOfWork = $this->app->make(UnitOfWork::class);
        $activity = $this->activity();
        $thrown = null;

        try {
            $unitOfWork->transactional(function () use ($repository, $activity): void {
                $repository->save($activity);

                throw new RuntimeException('Rollback transaction.');
            });
        } catch (RuntimeException $exception) {
            $thrown = $exception;
        }

        self::assertInstanceOf(RuntimeException::class, $thrown);
        self::assertSame('Rollback transaction.', $thrown->getMessage());
        $this->assertDatabaseMissing('activities', ['id' => $activity->id()->value()]);
    }

    private function activity(): Activity
    {
        return Activity::create(
            new ActivityId('00000000-0000-4000-8000-000000000001'),
            new ActivityCode('ACT-001'),
            new ActivityTitle('Inspect conveyor'),
            'Inspect the primary conveyor.',
            'Processing',
            'North tunnel',
            new ResponsibleId('123e4567-e89b-42d3-a456-426614174000'),
            ActivityPriority::HIGH,
            new ActivitySchedule(
                new DateTimeImmutable('2026-10-10'),
                new DateTimeImmutable('2026-10-12'),
            ),
        );
    }
}
