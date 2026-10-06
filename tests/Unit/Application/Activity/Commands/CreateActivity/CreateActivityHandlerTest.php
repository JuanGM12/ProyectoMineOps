<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Activity\Commands\CreateActivity;

use App\Application\Activity\Commands\CreateActivity\CreateActivityCommand;
use App\Application\Activity\Commands\CreateActivity\CreateActivityHandler;
use App\Application\Activity\DTOs\ActivityDTO;
use App\Application\Contracts\UnitOfWork;
use App\Domain\Activity\Entities\Activity;
use App\Domain\Activity\Enums\ActivityPriority;
use App\Domain\Activity\Exceptions\InvalidActivitySchedule;
use App\Domain\Activity\Repositories\ActivityRepository;
use DateTimeImmutable;
use Mockery\MockInterface;
use Tests\TestCase;

final class CreateActivityHandlerTest extends TestCase
{
    public function test_valid_command_persists_activity_in_transaction_and_returns_dto(): void
    {
        $repository = $this->mock(ActivityRepository::class, function (MockInterface $mock): void {
            $mock->shouldReceive('findByCode')->once()->andReturnNull();
            $mock->shouldReceive('save')
                ->once()
                ->withArgs(function (Activity $activity): bool {
                    self::assertSame('ACT-001', $activity->code()->value());
                    self::assertSame('Inspección del frente norte', $activity->title()->value());
                    self::assertSame('PENDING', $activity->status()->value);

                    return true;
                });
        });
        $unitOfWork = $this->mock(UnitOfWork::class, function (MockInterface $mock): void {
            $mock->shouldReceive('transactional')
                ->once()
                ->andReturnUsing(fn (callable $operation): mixed => $operation());
        });
        $handler = new CreateActivityHandler($repository, $unitOfWork);

        $result = $handler->handle($this->command());

        self::assertInstanceOf(ActivityDTO::class, $result);
        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $result->id,
        );
        self::assertSame('ACT-001', $result->code);
        self::assertSame('Inspección del frente norte', $result->title);
        self::assertSame('MEDIUM', $result->priority);
        self::assertSame('PENDING', $result->status);
        self::assertSame('2026-10-10', $result->scheduledDate);
        self::assertSame('2026-10-11', $result->dueDate);
    }

    public function test_domain_rule_prevents_persisting_invalid_activity(): void
    {
        $repository = $this->mock(ActivityRepository::class, function (MockInterface $mock): void {
            $mock->shouldReceive('findByCode')->once()->andReturnNull();
            $mock->shouldNotReceive('save');
        });
        $unitOfWork = $this->mock(UnitOfWork::class, function (MockInterface $mock): void {
            $mock->shouldReceive('transactional')
                ->once()
                ->andReturnUsing(fn (callable $operation): mixed => $operation());
        });
        $handler = new CreateActivityHandler($repository, $unitOfWork);
        $command = $this->command(
            priority: ActivityPriority::CRITICAL,
            dueDate: null,
        );

        $this->expectException(InvalidActivitySchedule::class);

        $handler->handle($command);
    }

    private function command(
        ActivityPriority $priority = ActivityPriority::MEDIUM,
        ?string $dueDate = '2026-10-11',
    ): CreateActivityCommand {
        return new CreateActivityCommand(
            code: 'ACT-001',
            title: 'Inspección del frente norte',
            description: 'Validar las condiciones del área.',
            area: 'Operaciones',
            location: 'Frente norte',
            responsibleId: 'd7794c47-cb29-4f1b-ab36-e5f7568a52fe',
            priority: $priority,
            scheduledDate: new DateTimeImmutable('2026-10-10'),
            dueDate: $dueDate === null ? null : new DateTimeImmutable($dueDate),
        );
    }
}
