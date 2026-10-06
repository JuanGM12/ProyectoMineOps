<?php
declare(strict_types=1);
namespace Tests\Feature\Infrastructure;
use App\Application\Contracts\UnitOfWork;
use App\Domain\Activity\Entities\Activity;
use App\Domain\Activity\Enums\ActivityPriority;
use App\Domain\Activity\Repositories\ActivityRepository;
use App\Domain\Activity\ValueObjects\ActivityCode;
use App\Domain\Activity\ValueObjects\ActivityId;
use App\Domain\Activity\ValueObjects\ActivitySchedule;
use App\Domain\Activity\ValueObjects\ActivityTitle;
use App\Domain\Activity\ValueObjects\ResponsibleId;
use App\Infrastructure\Persistence\EloquentActivityRepository;
use App\Infrastructure\Persistence\LaravelUnitOfWork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;
final class ActivityPersistenceTest extends TestCase
{
    use RefreshDatabase;
    public function test_activity_repository_persists_and_reconstitutes_domain_activity(): void
    {
        $repository = app(ActivityRepository::class);
        $activity = $this->activity();
        $repository->save($activity);
        $found = $repository->findById($activity->id());
        self::assertNotNull($found);
        self::assertSame($activity->id()->value(), $found->id()->value());
        self::assertSame('ACT-001', $found->code()->value());
        self::assertSame('Inspect conveyor', $found->title()->value());
        self::assertSame('2026-10-10', $found->schedule()->scheduledDate()->format('Y-m-d'));
        self::assertSame('2026-10-12', $found->schedule()->dueDate()?->format('Y-m-d'));
        self::assertSame($activity->id()->value(), $repository->findByCode(new ActivityCode('ACT-001'))?->id()->value());
        self::assertCount(1, $repository->findAll());
        $repository->remove($found);
        self::assertNull($repository->findById($activity->id()));
    }
    public function test_unit_of_work_rolls_back_database_changes_when_operation_fails(): void
    {
        $repository = app(ActivityRepository::class);
        $unitOfWork = app(UnitOfWork::class);
        $activity = $this->activity();
        try {
            $unitOfWork->transactional(function () use ($repository, $activity): void {
                $repository->save($activity);
                throw new RuntimeException('Rollback transaction.');
            });
        } catch (RuntimeException $exception) {
            self::assertSame('Rollback transaction.', $exception->getMessage());
        }
        self::assertNull($repository->findById($activity->id()));
    }
    public function test_service_container_binds_application_contracts_to_infrastructure(): void
    {
        self::assertInstanceOf(EloquentActivityRepository::class, app(ActivityRepository::class));
        self::assertInstanceOf(LaravelUnitOfWork::class, app(UnitOfWork::class));
    }
    private function activity(): Activity
    {
        return Activity::create(
            ActivityId::generate(),
            new ActivityCode('ACT-001'),
            new ActivityTitle('Inspect conveyor'),
            'Inspect the primary conveyor.',
            'Processing',
            'North tunnel',
            new ResponsibleId('123e4567-e89b-42d3-a456-426614174000'),
            ActivityPriority::HIGH,
            new ActivitySchedule(new \DateTimeImmutable('2026-10-10'), new \DateTimeImmutable('2026-10-12')),
        );
    }
}
