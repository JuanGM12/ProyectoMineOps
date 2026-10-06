<?php

declare(strict_types=1);

namespace App\Domain\Activity\ValueObjects;

use App\Domain\Activity\Exceptions\InvalidActivitySchedule;
use DateTimeImmutable;

final readonly class ActivitySchedule
{
    public function __construct(
        private DateTimeImmutable $scheduledDate,
        private ?DateTimeImmutable $dueDate,
    ) {
        if ($dueDate !== null && $dueDate < $scheduledDate) {
            throw InvalidActivitySchedule::dueDateBeforeScheduledDate();
        }
    }

    public function scheduledDate(): DateTimeImmutable
    {
        return $this->scheduledDate;
    }

    public function dueDate(): ?DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function equals(self $other): bool
    {
        return $this->scheduledDate == $other->scheduledDate
            && $this->dueDate == $other->dueDate;
    }
}
