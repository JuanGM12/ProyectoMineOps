<?php

declare(strict_types=1);

namespace App\Domain\Activity\Events;

use DateTimeImmutable;

final readonly class ResponsibleAssigned implements DomainEvent
{
    public DateTimeImmutable $occurredOn;

    public function __construct(
        public string $activityId,
        public string $responsibleId,
    ) {
        $this->occurredOn = new DateTimeImmutable;
    }

    public function aggregateId(): string
    {
        return $this->activityId;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
