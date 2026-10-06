<?php

declare(strict_types=1);

namespace App\Domain\Activity\Entities;

use App\Domain\Activity\Enums\ActivityPriority;
use App\Domain\Activity\Enums\ActivityStatus;
use App\Domain\Activity\Events\ActivityCancelled;
use App\Domain\Activity\Events\ActivityCompleted;
use App\Domain\Activity\Events\ActivityRescheduled;
use App\Domain\Activity\Events\ActivityStarted;
use App\Domain\Activity\Events\DomainEvent;
use App\Domain\Activity\Events\ResponsibleAssigned;
use App\Domain\Activity\Exceptions\InvalidActivitySchedule;
use App\Domain\Activity\Exceptions\InvalidActivityStatusTransition;
use App\Domain\Activity\ValueObjects\ActivityCode;
use App\Domain\Activity\ValueObjects\ActivityId;
use App\Domain\Activity\ValueObjects\ActivitySchedule;
use App\Domain\Activity\ValueObjects\ActivityTitle;
use App\Domain\Activity\ValueObjects\ResponsibleId;

final class Activity
{
    /** @var list<DomainEvent> */
    private array $domainEvents = [];

    private function __construct(
        private readonly ActivityId $id,
        private ActivityCode $code,
        private ActivityTitle $title,
        private ?string $description,
        private string $area,
        private string $location,
        private ResponsibleId $responsibleId,
        private ActivityPriority $priority,
        private ActivityStatus $status,
        private ActivitySchedule $schedule,
    ) {
        $this->ensureCriticalActivityHasDueDate($priority, $schedule);
    }

    public static function create(
        ActivityId $id,
        ActivityCode $code,
        ActivityTitle $title,
        ?string $description,
        string $area,
        string $location,
        ResponsibleId $responsibleId,
        ActivityPriority $priority,
        ActivitySchedule $schedule,
    ): self {
        return new self(
            $id,
            $code,
            $title,
            $description,
            trim($area),
            trim($location),
            $responsibleId,
            $priority,
            ActivityStatus::PENDING,
            $schedule,
        );
    }

    public static function reconstitute(
        ActivityId $id,
        ActivityCode $code,
        ActivityTitle $title,
        ?string $description,
        string $area,
        string $location,
        ResponsibleId $responsibleId,
        ActivityPriority $priority,
        ActivityStatus $status,
        ActivitySchedule $schedule,
    ): self {
        return new self(
            $id,
            $code,
            $title,
            $description,
            $area,
            $location,
            $responsibleId,
            $priority,
            $status,
            $schedule,
        );
    }

    public function start(): void
    {
        if (in_array($this->status, [ActivityStatus::COMPLETED, ActivityStatus::CANCELLED], true)) {
            throw InvalidActivityStatusTransition::fromTo($this->status, ActivityStatus::IN_PROGRESS);
        }

        if ($this->status === ActivityStatus::IN_PROGRESS) {
            return;
        }

        $this->status = ActivityStatus::IN_PROGRESS;
        $this->record(new ActivityStarted($this->id->value()));
    }

    public function complete(): void
    {
        if ($this->status === ActivityStatus::CANCELLED) {
            throw InvalidActivityStatusTransition::fromTo($this->status, ActivityStatus::COMPLETED);
        }

        if ($this->status === ActivityStatus::COMPLETED) {
            return;
        }

        $this->status = ActivityStatus::COMPLETED;
        $this->record(new ActivityCompleted($this->id->value()));
    }

    public function cancel(): void
    {
        if ($this->status === ActivityStatus::CANCELLED) {
            return;
        }

        $this->status = ActivityStatus::CANCELLED;
        $this->record(new ActivityCancelled($this->id->value()));
    }

    public function reschedule(ActivitySchedule $schedule): void
    {
        $this->ensureCriticalActivityHasDueDate($this->priority, $schedule);

        if ($this->schedule->equals($schedule)) {
            return;
        }

        $this->schedule = $schedule;
        $this->record(new ActivityRescheduled(
            $this->id->value(),
            $schedule->scheduledDate(),
            $schedule->dueDate(),
        ));
    }

    public function assignResponsible(ResponsibleId $responsibleId): void
    {
        if ($this->responsibleId->equals($responsibleId)) {
            return;
        }

        $this->responsibleId = $responsibleId;
        $this->record(new ResponsibleAssigned($this->id->value(), $responsibleId->value()));
    }

    public function changePriority(ActivityPriority $priority): void
    {
        $this->ensureCriticalActivityHasDueDate($priority, $this->schedule);
        $this->priority = $priority;
    }

    public function rename(ActivityTitle $title): void
    {
        $this->title = $title;
    }

    public function updateDetails(?string $description, string $area, string $location): void
    {
        $this->description = $description;
        $this->area = trim($area);
        $this->location = trim($location);
    }

    /** @return list<DomainEvent> */
    public function releaseDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    private function ensureCriticalActivityHasDueDate(ActivityPriority $priority, ActivitySchedule $schedule): void
    {
        if ($priority === ActivityPriority::CRITICAL && $schedule->dueDate() === null) {
            throw InvalidActivitySchedule::criticalActivityRequiresDueDate();
        }
    }

    private function record(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }

    public function id(): ActivityId
    {
        return $this->id;
    }

    public function code(): ActivityCode
    {
        return $this->code;
    }

    public function title(): ActivityTitle
    {
        return $this->title;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function area(): string
    {
        return $this->area;
    }

    public function location(): string
    {
        return $this->location;
    }

    public function responsibleId(): ResponsibleId
    {
        return $this->responsibleId;
    }

    public function priority(): ActivityPriority
    {
        return $this->priority;
    }

    public function status(): ActivityStatus
    {
        return $this->status;
    }

    public function schedule(): ActivitySchedule
    {
        return $this->schedule;
    }
}
