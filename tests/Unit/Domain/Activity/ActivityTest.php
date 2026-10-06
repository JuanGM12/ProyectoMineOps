<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Activity;

use App\Domain\Activity\Entities\Activity;
use App\Domain\Activity\Enums\ActivityPriority;
use App\Domain\Activity\Enums\ActivityStatus;
use App\Domain\Activity\Events\ActivityRescheduled;
use App\Domain\Activity\Events\ActivityStarted;
use App\Domain\Activity\Events\ResponsibleAssigned;
use App\Domain\Activity\Exceptions\InvalidActivitySchedule;
use App\Domain\Activity\Exceptions\InvalidActivityStatusTransition;
use App\Domain\Activity\Exceptions\InvalidValueObject;
use App\Domain\Activity\ValueObjects\ActivityCode;
use App\Domain\Activity\ValueObjects\ActivityId;
use App\Domain\Activity\ValueObjects\ActivitySchedule;
use App\Domain\Activity\ValueObjects\ActivityTitle;
use App\Domain\Activity\ValueObjects\ResponsibleId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ActivityTest extends TestCase
{
    public function test_title_is_required(): void
    {
        $this->expectException(InvalidValueObject::class);

        new ActivityTitle('   ');
    }

    public function test_due_date_cannot_be_before_scheduled_date(): void
    {
        $this->expectException(InvalidActivitySchedule::class);

        new ActivitySchedule(
            new DateTimeImmutable('2026-10-10'),
            new DateTimeImmutable('2026-10-09'),
        );
    }

    public function test_critical_activity_requires_due_date(): void
    {
        $this->expectException(InvalidActivitySchedule::class);

        $this->activity(
            priority: ActivityPriority::CRITICAL,
            schedule: new ActivitySchedule(new DateTimeImmutable('2026-10-10'), null),
        );
    }

    public function test_completed_activity_cannot_be_started_again(): void
    {
        $activity = $this->activity();
        $activity->start();
        $activity->complete();

        $this->expectException(InvalidActivityStatusTransition::class);

        $activity->start();
    }

    public function test_cancelled_activity_cannot_be_completed(): void
    {
        $activity = $this->activity();
        $activity->cancel();

        $this->expectException(InvalidActivityStatusTransition::class);

        $activity->complete();
    }

    public function test_pending_activity_cannot_be_completed_without_being_started(): void
    {
        $activity = $this->activity();

        $this->expectException(InvalidActivityStatusTransition::class);

        $activity->complete();
    }

    public function test_completed_activity_cannot_be_cancelled(): void
    {
        $activity = $this->activity();
        $activity->start();
        $activity->complete();

        $this->expectException(InvalidActivityStatusTransition::class);

        $activity->cancel();
    }

    public function test_cancelled_activity_cannot_be_started(): void
    {
        $activity = $this->activity();
        $activity->cancel();

        $this->expectException(InvalidActivityStatusTransition::class);

        $activity->start();
    }

    public function test_pending_activity_can_be_cancelled(): void
    {
        $activity = $this->activity();

        $activity->cancel();

        self::assertSame(ActivityStatus::CANCELLED, $activity->status());
    }

    public function test_in_progress_activity_can_be_completed(): void
    {
        $activity = $this->activity();
        $activity->start();

        $activity->complete();

        self::assertSame(ActivityStatus::COMPLETED, $activity->status());
    }

    public function test_in_progress_activity_can_be_cancelled(): void
    {
        $activity = $this->activity();
        $activity->start();

        $activity->cancel();

        self::assertSame(ActivityStatus::CANCELLED, $activity->status());
    }

    public function test_repeating_the_current_state_command_is_idempotent(): void
    {
        $activity = $this->activity();
        $activity->start();
        $activity->releaseDomainEvents();

        $activity->start();

        self::assertSame(ActivityStatus::IN_PROGRESS, $activity->status());
        self::assertSame([], $activity->releaseDomainEvents());
    }

    public function test_start_changes_status_and_records_event(): void
    {
        $activity = $this->activity();

        $activity->start();

        self::assertSame(ActivityStatus::IN_PROGRESS, $activity->status());
        self::assertContainsOnlyInstancesOf(ActivityStarted::class, $activity->releaseDomainEvents());
        self::assertSame([], $activity->releaseDomainEvents());
    }

    public function test_reschedule_changes_dates_and_records_event(): void
    {
        $activity = $this->activity();
        $schedule = new ActivitySchedule(
            new DateTimeImmutable('2026-10-12'),
            new DateTimeImmutable('2026-10-14'),
        );

        $activity->reschedule($schedule);

        self::assertTrue($activity->schedule()->equals($schedule));
        self::assertContainsOnlyInstancesOf(ActivityRescheduled::class, $activity->releaseDomainEvents());
    }

    public function test_assign_responsible_changes_responsible_and_records_event(): void
    {
        $activity = $this->activity();
        $responsible = new ResponsibleId('eb5a466f-f69d-4b69-a727-5dcb52fe9e39');

        $activity->assignResponsible($responsible);

        self::assertTrue($activity->responsibleId()->equals($responsible));
        self::assertContainsOnlyInstancesOf(ResponsibleAssigned::class, $activity->releaseDomainEvents());
    }

    public function test_activity_cannot_be_changed_to_critical_without_due_date(): void
    {
        $activity = $this->activity(
            schedule: new ActivitySchedule(new DateTimeImmutable('2026-10-10'), null),
        );

        $this->expectException(InvalidActivitySchedule::class);

        $activity->changePriority(ActivityPriority::CRITICAL);
    }

    public function test_planning_can_be_revised_to_critical_with_a_due_date(): void
    {
        $activity = $this->activity(
            priority: ActivityPriority::LOW,
            schedule: new ActivitySchedule(new DateTimeImmutable('2026-10-10'), null),
        );
        $schedule = new ActivitySchedule(
            new DateTimeImmutable('2026-10-12'),
            new DateTimeImmutable('2026-10-14'),
        );

        $activity->revisePlanning(ActivityPriority::CRITICAL, $schedule);

        self::assertSame(ActivityPriority::CRITICAL, $activity->priority());
        self::assertTrue($activity->schedule()->equals($schedule));
    }

    public function test_critical_planning_can_be_downgraded_and_remove_due_date(): void
    {
        $activity = $this->activity(priority: ActivityPriority::CRITICAL);
        $schedule = new ActivitySchedule(new DateTimeImmutable('2026-10-12'), null);

        $activity->revisePlanning(ActivityPriority::LOW, $schedule);

        self::assertSame(ActivityPriority::LOW, $activity->priority());
        self::assertNull($activity->schedule()->dueDate());
    }

    private function activity(
        ActivityPriority $priority = ActivityPriority::MEDIUM,
        ?ActivitySchedule $schedule = null,
    ): Activity {
        return Activity::create(
            ActivityId::generate(),
            new ActivityCode('ACT-001'),
            new ActivityTitle('Inspección del frente norte'),
            'Validar las condiciones del área.',
            'Operaciones',
            'Frente norte',
            new ResponsibleId('d7794c47-cb29-4f1b-ab36-e5f7568a52fe'),
            $priority,
            $schedule ?? new ActivitySchedule(
                new DateTimeImmutable('2026-10-10'),
                new DateTimeImmutable('2026-10-11'),
            ),
        );
    }
}
