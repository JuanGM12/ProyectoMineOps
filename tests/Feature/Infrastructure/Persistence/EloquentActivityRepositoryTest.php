<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Persistence;

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
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

final class EloquentActivityRepositoryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_it_persists_and_reconstitutes_the_complete_domain_activity(): void
    {
        $repository = $this->repository();
        $activity = $this->activity();

        $repository->save($activity);
        $found = $repository->findById($activity->id());

        self::assertNotNull($found);
        self::assertSame($activity->id()->value(), $found->id()->value());
        self::assertSame('ACT-001', $found->code()->value());
        self::assertSame('Inspect conveyor', $found->title()->value());
        self::assertSame('Inspect the primary conveyor.', $found->description());
        self::assertSame('Processing', $found->area());
        self::assertSame('North tunnel', $found->location());
        self::assertSame('123e4567-e89b-42d3-a456-426614174000', $found->responsibleId()->value());
        self::assertSame(ActivityPriority::HIGH, $found->priority());
        self::assertSame(ActivityStatus::PENDING, $found->status());
        self::assertSame('2026-10-10', $found->schedule()->scheduledDate()->format('Y-m-d'));
        self::assertSame('2026-10-12', $found->schedule()->dueDate()?->format('Y-m-d'));
        $this->assertDatabaseHas('activities', [
            'id' => $activity->id()->value(),
            'code' => 'ACT-001',
            'status' => ActivityStatus::PENDING->value,
        ]);
    }

    public function test_it_updates_an_existing_activity_without_creating_a_duplicate(): void
    {
        $repository = $this->repository();
        $activity = $this->activity();
        $repository->save($activity);
        $activity->start();

        $repository->save($activity);
        $found = $repository->findById($activity->id());

        self::assertNotNull($found);
        self::assertSame(ActivityStatus::IN_PROGRESS, $found->status());
        $this->assertDatabaseCount('activities', 1);
    }

    public function test_it_finds_an_activity_by_code(): void
    {
        $repository = $this->repository();
        $activity = $this->activity();
        $repository->save($activity);

        $found = $repository->findByCode(new ActivityCode('ACT-001'));

        self::assertNotNull($found);
        self::assertSame($activity->id()->value(), $found->id()->value());
        self::assertNull($repository->findByCode(new ActivityCode('ACT-999')));
    }

    public function test_it_returns_all_activities_in_deterministic_id_order(): void
    {
        $repository = $this->repository();
        $second = $this->activity('00000000-0000-4000-8000-000000000002', 'ACT-002');
        $first = $this->activity('00000000-0000-4000-8000-000000000001', 'ACT-001');
        $repository->save($second);
        $repository->save($first);

        $activities = $repository->findAll();

        self::assertSame(
            [$first->id()->value(), $second->id()->value()],
            array_map(static fn (Activity $activity): string => $activity->id()->value(), $activities),
        );
    }

    public function test_it_removes_an_activity(): void
    {
        $repository = $this->repository();
        $activity = $this->activity();
        $repository->save($activity);

        $repository->remove($activity);

        self::assertNull($repository->findById($activity->id()));
        $this->assertDatabaseMissing('activities', ['id' => $activity->id()->value()]);
    }

    public function test_database_rejects_duplicate_activity_codes(): void
    {
        $repository = $this->repository();
        $repository->save($this->activity('00000000-0000-4000-8000-000000000001', 'ACT-001'));

        $this->expectException(QueryException::class);

        $repository->save($this->activity('00000000-0000-4000-8000-000000000002', 'ACT-001'));
    }

    private function repository(): ActivityRepository
    {
        return $this->app->make(ActivityRepository::class);
    }

    private function activity(
        string $id = '00000000-0000-4000-8000-000000000001',
        string $code = 'ACT-001',
    ): Activity {
        return Activity::create(
            new ActivityId($id),
            new ActivityCode($code),
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
